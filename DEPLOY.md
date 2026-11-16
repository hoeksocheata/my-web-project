# របៀប Deploy ឱ្យមាន Link ពិត

## A. Docker (ក្នុងម៉ាស៊ីនខ្លួនឯង)
```
docker compose up --build
```
បើក http://localhost:8000 — មាន MySQL + ទិន្នន័យគំរូ + ប៊ូតុង Demo login រួចជាស្រេច។

## B. Render.com (ឥតគិតថ្លៃ)
1. Push គម្រោងទៅ GitHub (កុំ push `.env`)។
2. render.com → New → Blueprint → ជ្រើស repo (វាអាន `render.yaml`)។
3. បង្កើត APP_KEY៖ ដំណើរការ `php artisan key:generate --show` ក្នុងកុំព្យូទ័រ ហើយដាក់តម្លៃ `base64:...` ក្នុង Environment → `APP_KEY`។
4. ដាក់ `APP_URL` = link ដែល Render ផ្តល់ឱ្យ។ រង់ចាំ build ~៣-៥ នាទី។

⚠️ Free plan: SQLite ត្រូវ reset ពេល redeploy/ដេក ហើយ seed ឡើងវិញដោយស្វ័យប្រវត្តិ — សមល្អសម្រាប់ demo ប៉ុន្តែមិនមែនសម្រាប់ទិន្នន័យពិតទេ។ រូបអាប់ឡូដ (រូបគម្រប/ភស្តុតាង) ក៏បាត់ដែរ។

## Demo mode
`BOOKSTORE_DEMO=true` បង្ហាញប៊ូតុង «ចូលជា Admin / អតិថិជន» លើទំព័រ login។ **កុំបើកនៅហាងពិត** ព្រោះគណនី demo ប្រើពាក្យសម្ងាត់ `password`។
