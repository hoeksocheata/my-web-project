# BookStore REST API (Laravel Sanctum)

Base URL: `http://127.0.0.1:8000` · ទម្រង់: JSON · ផ្ញើ header `Accept: application/json`

| Method | Path | Auth | សិទ្ធិ | ពិពណ៌នា |
|---|---|---|---|---|
| POST | `/api/register` | — | — | បង្កើតគណនី (role = user) ទទួល token |
| POST | `/api/login` | — | — | ចូល ទទួល `access_token` |
| GET | `/api/books` | Bearer | ទាំងអស់ | បញ្ជីសៀវភៅ + ប្រភេទ |
| POST | `/api/books` | Bearer | Admin | បន្ថែមសៀវភៅ |
| DELETE | `/api/books/{id}` | Bearer | Admin | លុបសៀវភៅ |

ឧទាហរណ៍៖
```bash
TOKEN=$(curl -s -X POST localhost:8000/api/login -H "Accept: application/json" \
  -d email=admin@example.com -d password=password | jq -r .access_token)
curl -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" localhost:8000/api/books
```
កូដឆ្លើយតប៖ `201` បង្កើត · `401` មិនទាន់ login/ពាក្យសម្ងាត់ខុស · `403` មិនមែន Admin · `404` រកមិនឃើញ · `422` ទិន្នន័យមិនត្រឹមត្រូវ។

Postman៖ import `docs/bookstore.postman_collection.json` — ប៊ូតុង «Login (Admin)» រក្សា token ដោយស្វ័យប្រវត្តិ។
