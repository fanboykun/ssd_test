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
    supervisor \
    wget

# Install Cloud SQL Proxy
RUN wget https://storage.googleapis.com/cloud-sql-connectors/cloud-sql-proxy/v2.8.1/cloud-sql-proxy.linux.amd64 -O /usr/local/bin/cloud-sql-proxy \
    && chmod +x /usr/local/bin/cloud-sql-proxy

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

# Create startup script
RUN echo '#!/bin/bash\n\
echo "Current environment variables:"\n\
echo "DB_HOST=$DB_HOST"\n\
echo "DB_CONNECTION=$DB_CONNECTION"\n\
echo "Starting Cloud SQL Auth Proxy..."\n\
/usr/local/bin/cloud-sql-proxy --unix-socket=/cloudsql $PROJECT_ID:$REGION:ssd-test-db & \n\
echo "Starting PHP-FPM and Nginx..."\n\
supervisord -c /etc/supervisor/conf.d/supervisord.conf' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Generate application key if not exists
RUN if [ ! -f ".env" ]; then cp .env.example .env && php artisan key:generate; fi

# Cache configuration
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port 8080
EXPOSE 8080

# Set the entry point to our start script
CMD ["/usr/local/bin/start.sh"]
