import logging
import socket
import csv
import pytz
import json
from dnslib import DNSRecord, QTYPE, RR, A, TXT
from dnslib.server import DNSServer, BaseResolver, DNSLogger
from datetime import datetime
from dhooks import Webhook, Embed
from threading import Lock, Thread, Event
import time

def load_config():
    with open('data/config.json', 'r') as f:
        config = json.load(f)
    return config

config = load_config()

# Configuration from config.json
TZ = config.get("TZ", "America/New_York")
REDIRECT_PORTAL_IP = config.get("REDIRECT_PORTAL_IP", "127.0.0.1")
WEBHOOK_URL = config.get("WEBHOOK_URL", "")
DATA_WRITE_INTERVAL = config.get("DATA_WRITE_INTERVAL", 10)

upstream_dns = config.get("UPSTREAM_DNS", "1.1.1.1")
upstream_port = config.get("UPSTREAM_PORT", 53)
blocked_domains = set(config.get("BLOCKED_DOMAINS", []))

# Set up logging configuration
logging.basicConfig(level=logging.WARNING, format="%(asctime)s - %(message)s")
logger = logging.getLogger()

# Lock for thread-safe CSV writing
csv_lock = Lock()
csv_data = []
stop_event = Event()

class CustomResolver(BaseResolver):
    def resolve(self, request, handler):
        qname = request.q.qname
        qtype = QTYPE[request.q.qtype]
        client_ip = handler.client_address[0]

        # Log the request
        # logger.debug(f"Request: {qname} - Type: {qtype}")

        if any(domain in str(qname).lower() for domain in blocked_domains):
            self.send_webhook(client_ip, qname)
            return self.create_blocked_reply(request, qname)
        else:
            return self.forward_request(request, client_ip, qname, qtype)

    def send_webhook(self, client_ip, qname):
        if WEBHOOK_URL != "":
            hook = Webhook(WEBHOOK_URL)

            embed = Embed(
                description=f":no_entry_sign: blocked: {str(qname)}",
                color=0x5CDBF0,
                timestamp="now",
            )

            embed.set_author(name=client_ip)
            hook.send(embed=embed)

    def create_blocked_reply(self, request, qname):
        redirect_ip = REDIRECT_PORTAL_IP
        reply = request.reply()

        reply.add_answer(RR(qname, QTYPE.A, rdata=A(redirect_ip), ttl=60))

        return reply

    def forward_request(self, request, client_ip, qname, qtype):
        try:
            # Forward the request to upstream server
            sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            sock.settimeout(5)
            sock.sendto(request.pack(), (upstream_dns, upstream_port))

            # Receive the response from the upstream server
            response_data, _ = sock.recvfrom(512)
            response = DNSRecord.parse(response_data)

            # Add response data to in-memory list
            self.add_to_csv_data(
                f"{upstream_dns}:{upstream_port}",
                client_ip,
                qname,
                qtype,
                len(response_data),
            )

            return response
        except Exception as e:
            logger.error(f"Error forwarding request: {e}")
            # Return a standard error response
            reply = request.reply()
            reply.header.rcode = 2  # Server Failure
            return reply

    def add_to_csv_data(
        self, upstream_info, client_ip, qname, qtype, response_length_bytes
    ):
        timezone = pytz.timezone(TZ)
        current_time = datetime.now(timezone).strftime("%H:%M:%S")
        with csv_lock:
            csv_data.append(
                [
                    current_time,
                    client_ip,
                    str(qname),
                    str(qtype),
                    upstream_info,
                    str(response_length_bytes),
                ]
            )


def csv_writer_worker():
    csv_file_path = config.get("CSV_FILE_PATH", "data.csv")
    
    while not stop_event.is_set():
        with csv_lock:
            if csv_data:
                try:
                    with open(csv_file_path, "a", newline="") as csvfile:
                        csv_writer = csv.writer(csvfile)
                        csv_writer.writerows(csv_data)
                        csvfile.flush()  # Ensure data is written to disk
                        csv_data.clear()  # Clear the in-memory list after writing
                except Exception as e:
                    logger.error(f"Error writing to CSV: {e}")
        # Wait for 'DATA_WRITE_INTERVAL' seconds before the next write
        time.sleep(DATA_WRITE_INTERVAL)


if __name__ == "__main__":
    try:
        resolver = CustomResolver()
        dns_server = DNSServer(resolver, port=53, address="0.0.0.0")  # Without logger
        dns_server.start_thread()
        logger.info("DNS server started on port 53")

        csv_thread = Thread(target=csv_writer_worker)
        csv_thread.start()

        while True:
            time.sleep(1)  # Avoid busy-waiting
    except Exception as e:
        logger.error(f"Error starting DNS server: {e}")
    finally:
        stop_event.set()
        csv_thread.join()
