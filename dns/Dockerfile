FROM python:3.9-slim

# Set the working directory inside the container to the root directory
WORKDIR /

# Copy all files from the current directory to the container's root directory
COPY . /

# Install Python dependencies
RUN pip install dnslib dhooks pytz

# Set the default command to run the script
CMD ["python3", "dns.py"]
