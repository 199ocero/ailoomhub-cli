# AILoomHub CLI

CLI tool to use Notion as your knowledge base and use OpenAI models to generate and expand the knowledge.

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

### Setup

#### OpenAI API Key

Before starting, ensure you have set your OpenAI Secret Key in the `.env` file:

```dotenv
OPENAI_API_KEY=sk-tzY...
```

1. **Create User:** To create a user, run:
    ```bash
    php artisan make:user
    ```
    Note: For additional steps integrating with Notion, proceed to the Notion Integration section below.

2. **Notion Integration:** Create a Notion integration using the command:
    ```bash
    php artisan make:notion
    ```

3. **Embed Collection:** Generate an embed collection by running:
    ```bash
    php artisan make:embed-collection
    ```

4. **Retrieve Connected Pages:** Use the command to fetch all connected pages:
    ```bash
    php artisan make:page-retriever
    ```
    Note: If no pages are connected, follow the steps [here](https://www.notion.so/help/add-and-manage-connections-with-the-api) to establish connections.

5. **Create Text Embedding:** Generate text embedding with:
    ```bash
    php artisan make:embedding
    ```

6. **Use Embed Collection for Queries:** Utilize the embed collection to interact:
    ```bash
    php artisan ask:chatbot
    ```

### Notion Integration Steps

For integrating with Notion:
- Create an internal Notion integration [here](https://www.notion.so/my-integrations).
- Follow the steps mentioned above after creating the integration.


