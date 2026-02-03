# Hare & Tortoise Prospect Portal

An internal prospect research and outreach management platform for Hare and Tortoise Digital Agency.

## Features

- **Account Management**: Track potential prospects with pipeline stages
- **Automated Research**: AI-powered website analysis and signal detection
- **Lead Scoring**: Automatic scoring based on ICP fit, signals, and reachability
- **Brief Generation**: AI-generated research briefs for each prospect
- **Outreach Assets**: Auto-generated LinkedIn DMs and email templates
- **Exports**: CSV shortlists and ZIP outreach bundles
- **Lead Sources (Phase 2a)**: Discovery via search query packs (SerpAPI / Google). Create lead sources with seed queries; run manually or on a schedule; candidates are normalised, deduped, blocklisted, then promoted to accounts and research is queued.

## Tech Stack

- **Framework**: Laravel 12 + Livewire 3
- **Frontend**: Tailwind CSS + Alpine.js
- **Database**: MySQL 8
- **Cache/Queue**: Redis
- **AI**: OpenAI GPT-4o/GPT-4o-mini
- **Local Dev**: DDEV

## Requirements

- [DDEV](https://ddev.com/) (for local development)
- OpenAI API key

## Local Development Setup

### 1. Clone and Start DDEV

```bash
git clone <repository-url>
cd hare-and-tortoise-portal
ddev start
```

### 2. Install Dependencies

```bash
ddev composer install
ddev npm install
```

### 3. Configure Environment

```bash
cp .env.example .env
ddev exec php artisan key:generate
```

Edit `.env` and add your OpenAI API key:

```env
OPENAI_API_KEY=sk-your-api-key-here
```

### 4. Run Migrations and Seed

```bash
ddev exec php artisan migrate:fresh --seed
```

### 5. Build Assets

```bash
ddev npm run build
# Or for development with hot reload:
ddev npm run dev
```

### 6. Access the Application

Open [https://prospect-portal.ddev.site](https://prospect-portal.ddev.site)

Default login:
- Email: `zaq@hareandtortoise.agency`
- Password: `password`

## Key Commands

```bash
# Start DDEV
ddev start

# Stop DDEV
ddev stop

# Run migrations
ddev exec php artisan migrate

# Run queue worker (for research jobs)
# Note: Research jobs crawl prospect websites; the environment (e.g. DDEV) must have outbound HTTPS access.
ddev exec php artisan queue:work

# Code quality
ddev exec ./vendor/bin/pint        # Code style
ddev exec ./vendor/bin/phpstan analyse  # Static analysis

# Build assets
ddev npm run build
```

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `OPENAI_API_KEY` | OpenAI API key | Required |
| `OPENAI_MODEL_FAST` | Model for extraction/detection | `gpt-4o-mini` |
| `OPENAI_MODEL_QUALITY` | Model for briefs/outreach | `gpt-4o` |
| `AI_DAILY_SPEND_LIMIT` | Max daily AI spend (USD) | `5.00` |
| `RESEARCH_DAILY_LIMIT` | Max research runs per day | `50` |
| `CRAWL_FORCE_IPV4` | Force IPv4 for crawl/robots.txt (e.g. when IPv6 is broken in DDEV) | `false` |
| `CRAWL_VERIFY_SSL` | Verify SSL for crawler (set false in DDEV if you see SSL certificate errors) | `true` |
| `SERPAPI_API_KEY` | SerpAPI key (for Lead Sources discovery; Google results via SerpAPI) | Required for discovery |
| `DISCOVERY_APPROVE_BEFORE_IMPORT` | If true, discovery candidates require manual approval before promotion to accounts | `false` |
| `DISCOVERY_RATE_LIMIT_PER_MINUTE` | Max search API requests per minute per connector | `10` |

## Lead Sources (Discovery)

To use discovery (Phase 2a), add a SerpAPI key to `.env` (default provider; Bing Search APIs were retired Aug 2025):

```env
SERPAPI_API_KEY=your-serpapi-key
```

Get a key at [SerpAPI](https://serpapi.com/) (free tier available). Then in the app: **Lead Sources** → **Add Lead Source** → enter name and seed queries (e.g. `site:.org.uk training Brighton`). Use **Run now** to trigger a discovery run, or set cadence to Daily/Weekly for scheduled runs. If `DISCOVERY_APPROVE_BEFORE_IMPORT=true`, review candidates and approve before they become accounts; otherwise candidates are auto-promoted and research is queued.

## Troubleshooting – Research crawl fails in DDEV

If research jobs fail with "No pages could be fetched" (or "First error: Connection refused..."), outbound HTTPS from the DDEV web container is likely failing.

**Test outbound from the web container:**

```bash
ddev exec curl -sI https://pathwayhealthcare.org.uk
```

Expect `HTTP/2 200` or `3xx`. If you see "Connection refused" or "Could not resolve host", the issue is network or DNS from inside the container.

**Next steps:**

- **DDEV config:** Ensure [.ddev/config.yaml](.ddev/config.yaml) has `use_dns_when_possible: false` so the container uses its own DNS.
- **SSL certificate errors:** If the test shows SSL errors (e.g. "unable to get local issuer certificate"), set `CRAWL_VERIFY_SSL=false` in `.env` so the crawler skips SSL verification (DDEV/local only); then restart the queue worker.
- **IPv6 issues:** If DNS resolves but connection is still refused (e.g. broken IPv6), set `CRAWL_FORCE_IPV4=true` in `.env` and restart the queue worker.

The failed research run’s error message includes the first crawl error (e.g. "First error: Connection refused for URI https://...") to help narrow down the cause.

## Project Structure

```
app/
├── Enums/           # Status and type enums
├── Http/Controllers/
├── Jobs/            # Background jobs (research)
├── Livewire/        # Livewire components
├── Models/          # Eloquent models
└── Services/
    ├── AI/          # OpenAI integration
    ├── Crawler/     # Web scraping
    └── ScoringService.php

resources/
├── prompts/         # AI prompt templates (git-versioned)
│   ├── extractor/
│   ├── signal_detector/
│   ├── brief_generator/
│   └── outreach_writer/
└── views/           # Blade templates

database/
├── migrations/      # Database schema
└── seeders/         # Default data
```

## Data Model

- **Accounts**: Prospect companies with pipeline stages
- **Research Runs**: Research job instances for traceability
- **Account Sources**: Crawled page snapshots (stored on disk)
- **Signal Events**: Detected opportunities
- **AI Runs**: LLM call logs with cost tracking
- **Briefs**: Generated research summaries
- **Outreach Assets**: LinkedIn/email templates
- **ICPs**: Ideal Customer Profiles for scoring
- **Playbooks**: Outreach templates by angle

## Research Pipeline

1. **Crawl**: Politely fetch website pages (robots.txt, throttling)
2. **Extract**: AI extracts company info from text
3. **Detect**: AI identifies opportunities/signals
4. **Brief**: AI generates research summary
5. **Outreach**: AI writes personalised messages
6. **Score**: Calculate lead score based on ICP fit

## Exports

- **Shortlist CSV**: Top accounts by score
- **Outreach Bundle**: ZIP with briefs and messages per account

## License

Proprietary - Hare and Tortoise Digital Agency
