FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Install Node.js and pnpm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g pnpm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Copy package files
COPY package.json pnpm-lock.yaml ./

# Install composer dependencies
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# Install pnpm dependencies
RUN pnpm install --frozen-lockfile

# Copy the rest of the application
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Build assets
RUN pnpm run build

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Generate application key if not exists
RUN if [ ! -f ".env" ]; then cp .env.example .env && php artisan key:generate; fi

# Cache the application
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Install Cloud SQL Proxy
ADD https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 /cloud_sql_proxy
RUN chmod +x /cloud_sql_proxy

# Create startup script
RUN echo '#!/bin/bash\n\
echo "Starting Cloud SQL Proxy..."\n\
/cloud_sql_proxy -dir=/cloudsql -instances=silver-treat-443814-v3:asia-southeast2:ssd-test-db=tcp:3306 & \n\
echo "Starting supervisor..."\n\
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf\n\
' > /start.sh && chmod +x /start.sh

# Expose port 8080
EXPOSE 8080

# Start both services using the startup script
CMD ["/start.sh"]
