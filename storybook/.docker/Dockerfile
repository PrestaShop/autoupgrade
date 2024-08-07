# Storybook container requires both Node and PHP
# Using Node as base to avoid permission issues while running Storybook
FROM node:20

RUN apt update && apt install -y php8.2 php-dom
