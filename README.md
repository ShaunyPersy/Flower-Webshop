# Flower Webshop - HTML, JS, CSS, PHP, MySQL Application

This is a **flower webshop** built with **HTML**, **JavaScript**, **CSS**, **PHP**, and **MySQL**. The application allows users to browse flowers, view product details, add them to a cart, and complete the purchase.

## Features

- **Product Catalog**: Browse through different flower categories like roses, tulips, and daisies.
- **Shopping Cart**: Add products to your cart, view product details, and remove items.
- **Order Management**: Proceed to checkout, confirm your order, and make payments (simulated payment).
- **Admin Panel**: Admin users can manage products and categories.

## Technologies Used

- **HTML**: Markup language used to structure the website.
- **CSS**: Styling language used to design the layout and appearance.
- **JavaScript**: For dynamic interactions like showing/hiding payment forms and checking cart contents.
- **PHP**: Used for server-side logic, including handling user authentication, orders, and database interactions.
- **MySQL**: Database system used to store user, product, order, and review information.
- **XAMPP**: Local development environment used to run PHP and MySQL.

## Installation

1. Clone this repository:
    ```bash
    git clone https://github.com/ShaunyPersy/flower-webshop.git
    ```

2. Set up **XAMPP** and start the Apache and MySQL services.

3. Import the `PlantShop` database:
    - Open **phpMyAdmin** in your browser (usually at `http://localhost/phpmyadmin`).
    - Import the SQL schema and test data provided in the repository.

4. Configure the database connection:
    - Open the `functions.php` file and update the database connection details:
    ```php
    $host = "localhost";
    $user = "root";
    $password = "";  // Default MySQL password for XAMPP is empty
    $database = "PlantShop";
    ```

    In case you want to keep the original database connection details, then add an additional user account in XAMPP with:
     username: Webuser
     password: Lab2021
     host: localhost

## Running the Application

To run the application locally:
1. Place the project folder in the **htdocs** directory of your XAMPP installation (usually located at `C:\xampp\htdocs`).
2. Navigate to `http://localhost/flower-webshop` in your browser.

## Admin Panel

- **Admin Login**: Access the admin panel using the credentials provided in your database (or use a test admin account).
- **Manage Products**: Admin can add, edit, and delete flower products.
- **Manage Categories**: Admin can add and assign flowers to categories.

## Demo

https://github.com/user-attachments/assets/f5e36016-861d-434c-a804-80e7aa3c2eae

## Notes

- This project is meant for **local development** using **XAMPP** for testing purposes.
- For **production deployment**, ensure that sensitive information (like database credentials) is securely managed.
