FROM php:8.3-apache

# 1. Install dependencies & OpenSSH Server (Wajib untuk Azure SSH)
RUN apt-get update && apt-get install -y \
    git curl zip unzip libonig-dev libxml2-dev libzip-dev \
    openssh-server \
    && docker-php-ext-install pdo pdo_mysql zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ### TAMBAHAN BARU 1: Install Composer ###
# Mengambil program Composer dari image resminya
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 2. Konfigurasi SSH (Password harus 'Docker!' agar dikenali Azure)
RUN echo "root:Docker!" | chpasswd
COPY sshd_config /etc/ssh/sshd_config
RUN mkdir -p /var/run/sshd

# 3. Konfigurasi Apache & Laravel
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY . .

# ### TAMBAHAN BARU 2: Jalankan Composer Install ###
# Ini akan mendownload folder 'vendor' yang hilang
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 4. Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache


# COPY startup script dan beri izin eksekusi
COPY startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

# 5. Expose Port 80 (Web) dan 2222 (SSH Azure)
EXPOSE 80 2222

# Gunakan script ini sebagai perintah utama
ENTRYPOINT ["/usr/local/bin/startup.sh"]

# 6. Jalankan SSH service dan Apache secara bersamaan
CMD service ssh start && apache2-foreground
