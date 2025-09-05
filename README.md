# BeMo Ticket App — Quick Sail setup (≤10 steps)
1. Clone repository (SSH)
   - `git clone git@github.com:adamcalleja01/smart-ticket-triage-and-dashboard-adam-calleja.git && cd smart-ticket-triage-and-dashboard-adam-calleja`

2. Copy env and edit
   - `cp .env.example .env`
   - `code .`
   - Edit .env (DB, APP_URL, mail, queue, etc.)
   - Ensure that OPENAI_API_KEY, OPENAI_ORG and OPENAI_CLASSIFY_ENABLED are set to appropriate values

3. Install PHP deps (if vendor not present) or start Sail first (Ensure Docker is open on desktop)
   - `composer require laravel/sail --dev`
   - `./vendor/bin/sail up -d`

4. Ensure Composer is Installed (containers)
   - `./vendor/bin/sail composer install`

5. Install JS deps
   - `./vendor/bin/sail yarn install`

6. Generate app key
   - `./vendor/bin/sail artisan key:generate`

7. Run migrations & seed
   - `./vendor/bin/sail artisan migrate --seed`

8. Build assets and run development server
   - Prod: `./vendor/bin/sail npm run build`
   - Dev:  `./vendor/bin/sail npm run dev`

9. Start queue worker
    - Start a new terminal (Dont close the one from step 8)
    - `./vendor/bin/sail artisan queue:work`

Notes
- Stop containers: `./vendor/bin/sail down`

## Assumptions and Trade Offs
- Had to import data with API, would have passed it through as a prop
- UI is implemented with plain CSS and BEM instead of a component framework to keep the project self-contained
- No authentication implemented 
- Used enum classes to store status/categories
- Used PostreSQL as the database
- Built project using Laravel Sail

## What I would do with more time
- Install PrimeVue, and HeadlessUI for easier usage of Toasts, Modals, and other UI functions
- Use Tailwind CSS to make styling easier, as well as add more icons to the page (Easier to manage dark/light mode with external packages)
- Implement Broadcasting for updates for real time updates
- Add unit tests to ensure that functionality is secure and functional
- Implement permissions, policies, and authentication, as well as middleware.
- Rate limiting would be implemented
