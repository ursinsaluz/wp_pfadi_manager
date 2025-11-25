# Contributing to Pfadi-Aktivitäten Manager

Thank you for your interest in contributing to the Pfadi-Aktivitäten Manager plugin!

## Development Environment Setup

We provide a `docker-compose` setup to easily spin up a local development environment.

### Prerequisites

*   [Docker](https://www.docker.com/get-started)
*   [Docker Compose](https://docs.docker.com/compose/install/)

### Quick Start

1.  Open a terminal in the plugin directory.
2.  Run the following command to start the environment:
    ```bash
    docker-compose up -d
    ```
3.  Access the WordPress instance at: [http://localhost:8000](http://localhost:8000)
4.  Access the MailHog web interface (for email testing) at: [http://localhost:8025](http://localhost:8025)

### Database Credentials

*   **Host:** `db`
*   **Database:** `wordpress`
*   **User:** `wordpress`
*   **Password:** `password`

### Email Testing

The environment includes [MailHog](https://github.com/mailhog/MailHog) to capture all outgoing emails. You can view them in the web interface at [http://localhost:8025](http://localhost:8025).

### Code Style

Please ensure your code adheres to the WordPress Coding Standards. You can check this by running:

```bash
composer run lint
```

To automatically fix some issues:

```bash
composer run format
```
