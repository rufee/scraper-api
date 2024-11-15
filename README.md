# Laravel based URL scraping API

Scrapes a list of URLs according to specified CSS selectors

## Installation (Manual)

1. **Clone the repository and install dependencies:**
   ```bash
   composer install
   ```

2. **Host the application** using your preferred server or quickly test with:
   ```bash
   php artisan serve
   ```

3. **Run the queue worker** (if `QUEUE_DRIVER` is set to something other than `sync`):
   ```bash
   php artisan queue:work
   ```

## Usage

### Endpoints

1. **Create a New Scrape Job**
    - **Method**: `POST`
    - **Endpoint**: `/api/jobs`
    - **Description**: Queues a new scrape job for processing.
    - **Request Body** (JSON):
        - `urls` (array) **required**: URLs to scrape.
        - `selectors` (array) **required**: CSS selectors to filter the results.


2. **Retrieve a Job by ID**
    - **Method**: `GET`
    - **Endpoint**: `/api/jobs/{id}`
    - **Description**: Retrieves the details of a scrape job by its ID.
    - **Results**: Text values for matched selectors are returned for each URL along with HTTP status at scrape time. If a selector was not matched no result is returned for that selector.


3. **Delete a Job by ID**
    - **Method**: `DELETE`
    - **Endpoint**: `/api/jobs/{id}`
    - **Description**: Deletes the specified scrape job.

## Examples

### 1. **Create a Scrape Job**

**Request**:
- **POST** `/api/jobs`
- **Body**:
   ```json
   {
       "urls": [
           "https://example.com",
           "https://my-domain.tech"
       ],
       "selectors": [
           "title",
           "h1"
       ]
   }
   ```

**Response (Success)**:
```json
{
    "id": "01JCRSRA99D1S5SSGFX5V82D7X",
    "status": "queued",
    "urls": [
        "https://example.com",
        "https://my-domain.tech"
    ],
    "selectors": [
        "title",
        "h1"
    ],
    "results": []
}
```

**Response (Invalid selector)**:

```json
{
    "message": "Attribute selectors.0 must be a valid CSS selector",
    "errors": {
        "selectors.0": [
            "Attribute selectors.0 must be a valid CSS selector"
        ]
    }
}
```

### 2. **Retrieve a Job by ID**

**Request**:
- **GET** `/api/jobs/01JCRSRA99D1S5SSGFX5V82D7X`

**Response (Success)**:
```json
{
    "id": "01JCRSTM12460PC3BZ03P2S3D3",
    "status": "complete",
    "urls": [
        "https://example.com",
        "https://my-domain.tech"
    ],
    "selectors": [
        "title",
        "h1"
    ],
    "results": [
        {
            "url": "https://example.com",
            "http_status": 200,
            "data": [
                {
                    "selector": "title",
                    "text": "Example website"
                },
                {
                    "selector": "h1",
                    "text": "Heading 1"
                }
            ]
        },
        {
            "url": "https://my-domain.tech",
            "http_status": 404,
            "data": []
        }
    ]
}
```

**Response (Not found)**:

```json
{
    "status": 404,
    "message": "Job not found."
}
```

### 3. **Delete a Job by ID**

**Request**:
- **DELETE** `/api/jobs/01JCRSRA99D1S5SSGFX5V82D7X`

**Response (Success)**:
```json
{
    "status": 200,
    "message": "Job has been deleted."
}
```

**Response (Not found)**:

```json
{
    "status": 404,
    "message": "Job not found."
}
```
