# Use an official Python runtime as a parent image
FROM python:3.9-slim

# Set the working directory inside the container
WORKDIR /app

# Install djLint
RUN pip install djlint

# Copy the .djlintrc configuration file
COPY tests/djLint/.djlintrc ./

# Copy all .html.twig files from the views/templates directory
# COPY views/templates/*.html.twig ./views/templates/
COPY views/templates/ ./views/templates/

# Set the entry point to djLint for easy command execution
ENTRYPOINT ["djlint"]
