# Laravel Project

This is a Laravel application that interacts with a third-party API to fetch, store, and transform file and directory data.

## Prerequisites

- Docker and Docker Compose
- Composer

## Setup

Follow these steps to set up the project:

### 1. Clone the Repository

```
git clone https://github.com/viilance/bluegrid-dev-task.git
cd bluegrid-dev-task
```

### 2. Install Dependencies

```
composer install
```

### 3. Set Up Environment Variables

Copy the .env.example file to .env and customize it if needed:

```
cp .env.example .env
```

### 4. Start the Development Environment

```
./vendor/bin/sail up -d
```

### 5. Generate Application Key

```
./vendor/bin/sail artisan key:generate
```

### 6. Run Migrations

```
composer install
```

### 7. Run Tests

```
./vendor/bin/sail test
```

### 8. Run the Custom Command
To fetch, store, and transform the data from the third-party API, execute the custom command:
```
./vendor/bin/sail artisan app:fetch-files-and-directories
```

### Usage
The application provides an endpoint to fetch the transformed files and directories data,
as well as endpoints to fetch the saved directories and files in paginated format

#### API Endpoints
 - GET /files-and-directories
 - GET /directories
 - GET /files

