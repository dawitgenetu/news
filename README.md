git init# Borkena News

A modern news reporting website built with PHP and MySQL, featuring a clean and responsive design using Tailwind CSS.

## Features

- Responsive news article display
- Featured articles section
- Trending articles section
- Category-based article filtering
- User-friendly navigation
- Clean and modern UI design

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- XAMPP (recommended for local development)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/borkena-news.git
```

2. Set up your web server (XAMPP):
   - Place the project in your `htdocs` directory
   - Start Apache and MySQL services

3. Create the database:
   - Open phpMyAdmin
   - Create a new database named `borkena_news`
   - Import the database schema from `database/schema.sql`

4. Configure the database connection:
   - Copy `config/database.example.php` to `config/database.php`
   - Update the database credentials in `config/database.php`

5. Access the website:
   - Open your browser
   - Navigate to `http://localhost/borkena-news`

## Project Structure

```
borkena-news/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── article_functions.php
├── database/
│   └── schema.sql
├── index.php
└── README.md
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

Your Name - your.email@example.com

Project Link: [https://github.com/yourusername/borkena-news](https://github.com/yourusername/borkena-news) 