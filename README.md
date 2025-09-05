# BeMo Ticket App — Quick Sail setup (≤10 steps)
1. Clone repository (SSH)
   - git clone git@github.com:adamcalleja01/smart-ticket-triage-and-dashboard-adam-calleja.git && cd smart-ticket-triage-and-dashboard-adam-calleja

2. Copy env and edit
   - cp .env.example .env
   - Edit .env (DB, APP_URL, mail, queue, etc.)
   - Ensure that OPENAI_API_KEY, OPENAI_ORG and OPENAI_CLASSIFY_ENABLED are set to appropriate values

3. Install PHP deps (if vendor not present) or start Sail first
   - ./vendor/bin/sail composer install

4. Start Sail (containers)
   - ./vendor/bin/sail up -d

5. Install JS deps
   - ./vendor/bin/sail yarn install

6. Generate app key
   - ./vendor/bin/sail artisan key:generate

7. Run migrations & seed
   - ./vendor/bin/sail artisan migrate --seed

8. Build assets
   - Dev:  ./vendor/bin/sail npm run dev
   - Prod: ./vendor/bin/sail npm run build

9. Start queue worker 
    - ./vendor/bin/sail artisan queue:work

Notes
- Stop containers: ./vendor/bin/sail down

## Assumptions and Trade Offs
- Had to import data with API, would have passed it through as a prop
- UI is implemented with plain CSS and BEM instead of a component framework to keep the project self-contained
- No authentication implemented 

## What I would do with more time
- Install PrimeVue, and HeadlessUI for easier usage of Toasts, Modals, and other UI functions
- Use Tailwind CSS to make styling easier, as well as add more icons to the page
- Implement Broadcasting for updates for real time updates
- Add unit tests to ensure that functionality is secure and functional
- Implement permissions, policies, and authentication, as well as middleware.
