# AILoomHub CLI

Command Line Interface (CLI) tool designed for utilizing Notion as your knowledge base, with the capability to leverage OpenAI models for generating and enhancing information.

## Installation

### Cloning the Repository

Clone the repository to your local environment:

```bash
git clone https://github.com/199ocero/ailoomhub-cli.git
cd ailoomhub-cli
```

Ensure you have Composer installed. If not, get it [here](https://getcomposer.org/download/).

Run the following command in the project directory:

```bash
composer install
```

### PostgreSQL Setup

To get started, make sure you have PostgreSQL installed on your system. If you don't have it installed yet, you can download it [here](link_to_download).

#### Creating a Database

Once PostgreSQL is installed, create a database for your project by following these steps:

1. Open your PostgreSQL environment.
2. Use the appropriate commands to create a new database.

#### Installing pgvector Extension

To enable vector similarity search in your PostgreSQL database, you'll need to install the `pgvector` extension. Follow these instructions:

1. Refer to the [official documentation](link_to_pgvector_docs) for the `pgvector` extension.
2. Follow the provided instructions to install the extension in your PostgreSQL environment.

After completing these steps, your PostgreSQL setup should be ready for use with the `pgvector` extension.


### Setup

#### OpenAI API Key

Before starting, ensure you have set your OpenAI Secret Key in the `.env` file:

```dotenv
OPENAI_API_KEY=sk-tzY...
```

### Prompt Steps

1. **Create User:** To create a user, run:
    ```bash
    php artisan make:user
    ```
2. **Notion Integration:** Create a Notion integration using the command:
    ```bash
    php artisan make:notion
    ```
    Note: Create an internal Notion integration [here](https://www.notion.so/my-integrations).
   
4. **Embed Collection:** Generate an embed collection by running:
    ```bash
    php artisan make:embed-collection
    ```

5. **Retrieve Connected Pages:** Use the command to fetch all connected pages:
    ```bash
    php artisan make:page-retriever
    ```
    Note: If no pages are connected, follow the steps [here](https://www.notion.so/help/add-and-manage-connections-with-the-api) to establish connections.

6. **Create Text Embedding:** Generate text embedding with:
    ```bash
    php artisan make:embedding
    ```

7. **Ask a Chatbot:** You can now test if your chatbot is working:
    ```bash
    php artisan ask:chatbot
    ```


