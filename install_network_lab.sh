#!/bin/bash

# Network Lab Install Script
# This script installs a web server, clones the Network Web Lab repository,
# and sets up symlinks for the project.

# Variables
REPO_URL="https://github.com/Pantakit-Joseph/network-web-lab.git"
PROJECT_DIR="/var/network-web-lab"
WEB_ROOT="/var/www/html"

# Function to install web server based on distribution
install_web_server() {
    if command -v apt &>/dev/null; then
        # Debian/Ubuntu-based distributions
        sudo apt update
        sudo apt install -y apache2 php libapache2-mod-php git
        sudo systemctl enable apache2
        sudo systemctl start apache2
    elif command -v yum &>/dev/null; then
        # CentOS/RHEL-based distributions
        sudo yum install -y httpd php git
        sudo systemctl enable httpd
        sudo systemctl start httpd
    elif command -v dnf &>/dev/null; then
        # Fedora-based distributions
        sudo dnf install -y httpd php git
        sudo systemctl enable httpd
        sudo systemctl start httpd
    elif command -v pacman &>/dev/null; then
        # Arch Linux-based distributions
        sudo pacman -Syu --noconfirm apache php git
        sudo systemctl enable httpd
        sudo systemctl start httpd
    else
        echo "Unsupported distribution. Please install Apache and PHP manually."
        exit 1
    fi
}

# Function to clone the repository
clone_repository() {
    echo "Cloning Network Web Lab repository..."
    sudo git clone "$REPO_URL" "$PROJECT_DIR"
    if [ $? -ne 0 ]; then
        echo "Failed to clone the repository. Please check your internet connection."
        exit 1
    fi
    echo "Repository cloned successfully."
}

# Function to create symlinks
create_symlinks() {
    echo "Creating symlinks in $WEB_ROOT..."
    for file in "$PROJECT_DIR"/src/*; do
        sudo ln -sf "$file" "$WEB_ROOT/"
    done
    echo "Symlinks created successfully."
}

# Main script
echo "Starting Network Web Lab installation..."

# Step 1: Install web server
echo "Installing web server..."
install_web_server

# Step 2: Clone the repository
clone_repository

# Step 3: Create symlinks
create_symlinks

# Step 4: Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data "$PROJECT_DIR" # For Debian/Ubuntu
sudo chown -R apache:apache "$PROJECT_DIR"     # For CentOS/RHEL/Fedora
sudo chown -R http:http "$PROJECT_DIR"         # For Arch Linux

# Step 5: Restart web server
echo "Restarting web server..."
if command -v systemctl &>/dev/null; then
    sudo systemctl restart apache2 || sudo systemctl restart httpd
fi

echo "Installation completed successfully!"
echo "You can access the Network Web Lab at http://localhost/"
