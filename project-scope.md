# Hare & Tortoise Prospect Portal

> **Development Standards**: This project follows the cursor rules defined in `.cursor/rules/`. MVP prioritises speed with core standards (PSR-12, PHPStan, Pint, security basics). Testing and full WCAG compliance deferred to Phase 2.

## Project Overview

### What You're Building

A lightweight "prospecting engine" that:

- **Ingests targets** (companies/people) from lists + the web
- **Detects signals** (intent + pain)
- **Generates an account brief + micro-audit**
- **Outputs outreach assets** (LinkedIn DM/email + follow-ups)
- **Tracks outcomes** and improves your scoring over time

It's basically a CRM + research assistant + content generator, but _opinionated_ around your Hare & Tortoise approach.

---

## Technical Architecture

### Stack Decision

| Layer | Technology | Rationale |
|-------|------------|-----------|
| Framework | Laravel 12 (PHP 8.3) | Your primary stack, AI-assisted development |
| Local Dev | DDEV | Docker-based, consistent environments |
| Frontend | Livewire 3 + Alpine.js | Rapid development, minimal JS overhead |
| Styling | Tailwind CSS + BEM methodology | Matches agency standards, maintainable CSS |
| Database | MySQL 8+ | Reliable, Laravel Cloud compatible |
| Queue | Redis + Laravel Horizon | Job monitoring, rate limiting |
| AI Provider | OpenAI API (GPT-4o + GPT-4o-mini) | Your existing account, model mixing for cost optimisation |
| Scraping | Laravel HTTP + Browsershot (fallback) | Simple pages + JS-rendered fallback |
| Hosting | Laravel Cloud | Your proven deployment platform |
| File Storage | Local (MVP) → S3/R2 (later) | Store scraped HTML, exports |
| Testing | Pest PHP + Laravel Dusk | Primary test framework (deferred for MVP) |

### Code Standards (per .cursor/rules)

| Standard | Requirement |
|----------|-------------|
| PHP Style | PSR-12 compliance, enforced via Laravel Pint |
| Type Safety | Strict typing, return type declarations on all methods |
| Static Analysis | PHPStan at repository configured level |
| Documentation | PHPDoc comments for all public methods |
| Validation | Form Request classes for all user input |
| Responses | API Resources for consistent JSON output |

### Architecture Patterns

Following the cursor rules, implement these Laravel patterns:

- **Service Layer**: Encapsulate business logic in `app/Services/`
- **Repository Pattern**: Data access abstraction where beneficial
- **Form Requests**: All validation in dedicated request classes
- **API Resources**: Transform models for API responses
- **Events & Listeners**: Decouple components (e.g., `AccountResearched` event)
- **Jobs**: Queue all AI and scraping work

### Authentication & Authorization

- **Auth**: Laravel Breeze (simple, Livewire-compatible)
- **Single-user MVP**: No teams/multi-tenancy initially
- **Future-proof**: Add `team_id` to core tables for later expansion
- **Policies**: Implement authorization policies for all resources

### Security & Compliance (OWASP Top 10)

| Concern | Implementation |
|---------|----------------|
| Input Validation | Form Request classes with sanitization |
| Output Encoding | Blade's automatic escaping, no raw HTML |
| SQL Injection | Eloquent ORM exclusively, no raw queries |
| XSS Prevention | CSP headers, proper output encoding |
| CSRF Protection | Laravel's built-in CSRF tokens on all forms |
| Security Headers | CSP, HSTS, X-Frame-Options, X-Content-Type-Options |
| API Keys | Encrypted in `.env`, never in database or logs |
| GDPR | Contact data is business context (legitimate interest), add data retention policy |
| Data Retention | Auto-archive accounts untouched for 12 months |
| Rate Limiting | Queue-based throttling for scraping + AI calls |
| Audit Trail | `ai_runs` table logs all AI interactions with costs |
| Session Security | Database session driver, secure cookie settings |

---

## The Repeatable Workflow

### Stage 0 - Setup

**You define once:**

- ICP(s): e.g. "UK professional services, education, charities, SaaS - 10-250 staff - marketing site + at least one internal system - signs of messy UX / governance / delivery bottlenecks"
- Offer angles: e.g.
  - "Accessibility + UX consistency"
  - "AI-enabled content operations"
  - "Modernising the stack without risky rebuilds"
- Your tone + boundaries (no spam, no hype, always specific)

**Platform stores these as:**

- `ideal_customer_profiles`
- `playbooks` (angles + rules + templates)

### Stage 1 - Target Universe Definition (monthly)

**Input:** a list of companies (CSV/manual) + "sources" you regularly check  
**AI task:** expand lookalikes + enrich basic info

**Output:**

- 50-200 target accounts in a pipeline
- Basic enrichment: sector, size estimate, locations, key pages, socials

**Platform tasks:**

- De-dupe (by domain normalisation)
- Create "Account" records
- Create "Research jobs" to build initial briefs

### Stage 2 - Signal Scanning (weekly)

For each account, the system checks for signals like:

**Website signals**

- Performance issues, inconsistent IA, poor accessibility indicators
- Outdated UI patterns
- Content bloat / unclear journeys (common in education/public sector)
- Multiple subdomains with different design systems

**Business signals (optional - Phase 2)**

- Hiring for "Digital/Transformation/AI"
- New initiatives / pilots
- Leadership posting about AI adoption

**Output:**

- A ranked "Hot / Warm / Cold" list
- Each with "why now" bullets

**Platform tasks:**

- Update `signal_events`
- Recompute `lead_score`
- Surface a "weekly action list"

### Stage 3 - Account Brief + Micro-audit (per lead)

For "Warm/Hot" accounts:

**AI produces:**

- 1-page account brief (exec readable)
- Micro-audit (5-10 observations with evidence links)
- Recommended angle + CTA (offer audit call / quick fixes / roadmap)

**Platform stores:**

- `briefs` (versioned)
- `signal_events` (structured findings)
- Recommendations linked to `playbooks`

### Stage 4 - Personalised Outreach (low volume)

For each selected lead:

**AI outputs:**

- LinkedIn DM variant(s)
- Email variant(s)
- Follow-up sequence
- "First touch" vs "after they engaged" versions

**Quality enforcement:**

- Must reference 2+ specific observations
- Max length: DM 300 chars, Email 150 words
- "No AI hype" constraint in prompt
- One clear CTA per message

### Stage 5 - Nurture & Feedback Loop

You log outcomes:

- `new` → `contacted` → `replied` → `meeting` → `proposal` → `won` / `lost`
- Reason tags (e.g., "budget", "timing", "wrong fit")

System learns (Phase 2):

- Which signals correlate with replies
- Which playbooks convert
- Which sectors are high friction

---

## MVP Scope (Phase 1)

### MVP Goal

**Give you a weekly list of 20 accounts, ranked + briefed, with outreach ready to send.**

### MVP Features with Acceptance Criteria

#### 1. Authentication & Dashboard
- [ ] Login with email/password (Laravel Breeze)
- [ ] Dashboard with status summary cards (counts, not charts)
- [ ] Limit/budget status indicator
- [ ] Hot leads table (top 20)
- [ ] Simple navigation: Accounts, ICPs, Playbooks, Settings

#### 2. Account Management
- [ ] CRUD for accounts (name, URL, sector, size band, notes)
- [ ] **Separated statuses**: `pipeline_stage` (sales) + `research_status` (system)
- [ ] CSV import with validation (required: name, URL; optional: sector, size)
- [ ] De-duplication by normalised domain (strip www, trailing slashes)
- [ ] Filter/search by pipeline stage, research status, sector, score range

#### 3. ICP & Playbook Setup
- [ ] CRUD for ICPs (name, description, sector targets, size bands, key signals)
- [ ] CRUD for Playbooks (name, angle, DM template, email template, constraints)
- [ ] Seed with 1 default ICP + 2 playbooks matching agency positioning
- [ ] Prompts stored as versioned files in `resources/prompts/` (git-tracked)

#### 4. Research Pipeline
- [ ] "Run Research" button per account → creates `research_run`, queues job
- [ ] Bulk "Research All New" action with limit checking
- [ ] **Polite crawling**: robots.txt check (cached), per-domain throttle, size limits
- [ ] Job fetches: homepage, /about, /services, /contact, /careers (if exist)
- [ ] **Disk storage**: HTML/text saved to files, paths stored in DB
- [ ] Handle failures gracefully (timeout, 404, blocked by robots.txt)
- [ ] All artifacts linked to `research_run` for traceability

#### 5. AI Analysis
- [ ] Model mixing: gpt-4o-mini for extraction, gpt-4o for outreach
- [ ] Extractor prompt: parse page content → structured facts
- [ ] Signal detector: **text-grounded heuristics only** (content, UX, tech, opportunity)
- [ ] Brief generator: 1-page account summary in markdown
- [ ] Outreach writer: LinkedIn DM + email draft per playbook
- [ ] **Strict schema validation** on all AI outputs
- [ ] Graceful handling of invalid outputs (log, continue with partial data)
- [ ] All AI runs logged with model, cost, validation status

#### 6. Rate Limiting & Budget Control
- [ ] Daily account limit (default: 50)
- [ ] Daily spend limit (default: £5)
- [ ] Clear `blocked_by_budget` / `blocked_by_limit` statuses
- [ ] Dashboard indicator when limits reached
- [ ] Limits reset at midnight UTC

#### 7. Scoring & Weekly List
- [ ] Compute lead score (ICP fit 0-40, signals 0-40, reachability 0-20)
- [ ] Store breakdown as JSON for transparency
- [ ] "Weekly Shortlist" view: top 20 by score, filterable

#### 8. Outreach Review
- [ ] View generated DM/email per account
- [ ] Edit before copying (human-in-loop)
- [ ] Mark as "sent" with date (manual tracking)
- [ ] Add notes/outcome after contact

#### 9. Exports
- [ ] Weekly shortlist CSV download
- [ ] Outreach bundle ZIP (per-account folders with brief + messages)

### Deferred to Phase 2

| Feature | Reason |
|---------|--------|
| DOM-based accessibility signals | Requires headless browser, complexity |
| Performance/Lighthouse signals | Requires headless browser, API costs |
| LinkedIn scraping | Legal complexity |
| Third-party enrichment APIs | Cost, complexity - manual input sufficient for MVP |
| Auto-sending | Keep human in loop for quality control |
| Contact management | Focus on accounts first |
| Analytics/learning | Need data first before optimisation |
| Teams/multi-user | Single-user MVP |
| Charts/dashboards | Table-first approach for MVP |
| DB-editable prompts | Git-versioned files sufficient for MVP |

---

## Data Model (Laravel-Ready)

### Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    users    │       │    icps     │       │  playbooks  │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │       │ id          │       │ id          │
│ name        │       │ name        │       │ name        │
│ email       │       │ description │       │ angle       │
│ password    │       │ sectors[]   │       │ dm_template │
│ timestamps  │       │ size_bands[]│       │ email_templ │
└─────────────┘       │ signals[]   │       │ constraints │
       │              │ weights{}   │       │ is_active   │
       │              │ timestamps  │       │ timestamps  │
       ▼              └─────────────┘       └─────────────┘
┌─────────────────────────────────────────────────────────┐
│                       accounts                          │
├─────────────────────────────────────────────────────────┤
│ id, user_id(FK), name, url, domain(unique, normalised) │
│ sector, size_band, location, notes                     │
│ pipeline_stage(enum), research_status(enum)            │
│ lead_score, score_breakdown{}, last_researched_at      │
│ timestamps, deleted_at(soft delete)                    │
└─────────────────────────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────────────────────┐
│                    research_runs                        │
├─────────────────────────────────────────────────────────┤
│ id, account_id(FK), status(enum), triggered_by         │
│ started_at, completed_at, error_message                │
│ total_cost, pages_fetched, signals_found               │
│ timestamps                                             │
└─────────────────────────────────────────────────────────┘
       │
       ├──────────────┬──────────────┬──────────────┐
       ▼              ▼              ▼              ▼
┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
│account_     │ │signal_      │ │   briefs    │ │ outreach_   │
│sources      │ │events       │ │             │ │ assets      │
├─────────────┤ ├─────────────┤ ├─────────────┤ ├─────────────┤
│ research_   │ │ research_   │ │ research_   │ │ account_id  │
│ run_id(FK)  │ │ run_id(FK)  │ │ run_id(FK)  │ │ playbook_id │
│ account_id  │ │ account_id  │ │ account_id  │ │ channel     │
│ type(enum)  │ │ type        │ │ version     │ │ content     │
│ url         │ │ severity    │ │ content_md  │ │ status      │
│ fetched_at  │ │ title       │ │ facts{}     │ │ sent_at     │
│ html_path   │ │ evidence_url│ │ created_at  │ │ timestamps  │
│ text_path   │ │ snippet     │ └─────────────┘ └─────────────┘
│ html_hash   │ │ score_impact│
│ byte_size   │ │ detected_at │
│ status      │ └─────────────┘
└─────────────┘
       │
       ▼
┌─────────────────────────────────────────────────────────┐
│                       ai_runs                           │
├─────────────────────────────────────────────────────────┤
│ id, research_run_id(FK), account_id(FK)                │
│ run_type(enum), model, prompt_version                  │
│ inputs{}, outputs{}, validation_status                 │
│ tokens_in, tokens_out, cost_est, duration_ms           │
│ created_at                                             │
└─────────────────────────────────────────────────────────┘
```

### Key Design Decisions

| Decision | Implementation |
|----------|----------------|
| HTML/text storage | Files on disk, paths stored in DB (avoids bloat) |
| Research traceability | All artifacts linked to `research_runs` |
| Status separation | `pipeline_stage` (sales) vs `research_status` (technical) |
| AI output validation | JSON schema validation, graceful failure handling |
| Prompt templates | Versioned files in `resources/prompts/` (git-tracked) |

### Table Specifications

#### Core Tables

```php
// users - Laravel Breeze default
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});

// accounts - primary entity
Schema::create('accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('url');
    $table->string('domain')->unique(); // normalised: example.com
    $table->string('sector')->nullable();
    $table->string('size_band')->nullable(); // 1-10, 11-50, 51-250, 250+
    $table->string('location')->nullable();
    $table->text('notes')->nullable();
    
    // Separated concerns: sales stage vs research state
    $table->string('pipeline_stage')->default('new'); // new, contacted, replied, meeting, proposal, won, lost
    $table->string('research_status')->default('pending'); // pending, queued, running, completed, failed, blocked_by_budget, blocked_by_limit
    
    $table->integer('lead_score')->default(0);
    $table->json('score_breakdown')->nullable();
    $table->timestamp('last_researched_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['pipeline_stage', 'lead_score']);
    $table->index('research_status');
    $table->index('sector');
});

// research_runs - groups all research artifacts for traceability
Schema::create('research_runs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('account_id')->constrained()->cascadeOnDelete();
    $table->string('status')->default('pending'); // pending, running, completed, failed, cancelled
    $table->string('triggered_by')->default('manual'); // manual, scheduled, bulk
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->text('error_message')->nullable();
    $table->decimal('total_cost', 10, 6)->default(0);
    $table->integer('pages_fetched')->default(0);
    $table->integer('signals_found')->default(0);
    $table->timestamps();
    
    $table->index(['account_id', 'status']);
    $table->index('created_at');
});

// icps - ideal customer profiles
Schema::create('icps', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->json('sectors')->nullable(); // ["education", "professional-services"]
    $table->json('size_bands')->nullable(); // ["11-50", "51-250"]
    $table->json('signals')->nullable(); // signals to look for
    $table->json('scoring_weights')->nullable();
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});

// playbooks - outreach strategies
Schema::create('playbooks', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('angle'); // e.g., "Accessibility + UX"
    $table->text('dm_template');
    $table->text('email_template');
    $table->json('constraints')->nullable(); // max_length, required_signals, etc.
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Research & Signal Tables

```php
// account_sources - scraped page data (content stored on disk)
Schema::create('account_sources', function (Blueprint $table) {
    $table->id();
    $table->foreignId('research_run_id')->constrained()->cascadeOnDelete();
    $table->foreignId('account_id')->constrained()->cascadeOnDelete();
    $table->string('type'); // homepage, about, services, contact, careers
    $table->string('url');
    $table->timestamp('fetched_at')->nullable();
    
    // File paths (content stored on disk to avoid DB bloat)
    $table->string('html_path')->nullable(); // storage/app/snapshots/{account_id}/{run_id}/{type}.html
    $table->string('text_path')->nullable(); // storage/app/snapshots/{account_id}/{run_id}/{type}.txt
    $table->string('html_hash', 64)->nullable(); // detect changes between runs
    $table->integer('byte_size')->default(0); // for monitoring
    
    $table->string('status')->default('pending'); // pending, success, failed, skipped
    $table->text('error_message')->nullable();
    $table->timestamps();
    
    $table->unique(['research_run_id', 'type']);
    $table->index('account_id');
});

// signal_events - detected issues/opportunities
Schema::create('signal_events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('research_run_id')->constrained()->cascadeOnDelete();
    $table->foreignId('account_id')->constrained()->cascadeOnDelete();
    $table->string('type'); // content, ux, tech, opportunity (text-grounded only for MVP)
    $table->string('severity'); // high, medium, low
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('evidence_url')->nullable();
    $table->text('evidence_snippet')->nullable(); // exact text that triggered signal
    $table->integer('score_impact')->default(0);
    $table->timestamp('detected_at');
    $table->timestamps();
    
    $table->index(['account_id', 'severity']);
    $table->index('research_run_id');
});
```

#### AI Output Tables

```php
// ai_runs - audit trail for all AI calls
Schema::create('ai_runs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('research_run_id')->constrained()->cascadeOnDelete();
    $table->foreignId('account_id')->constrained()->cascadeOnDelete();
    $table->string('run_type'); // extract, detect_signals, brief, outreach
    $table->string('model'); // gpt-4o, gpt-4o-mini
    $table->string('prompt_version')->default('v1'); // matches filename in resources/prompts/
    $table->json('inputs')->nullable();
    $table->json('outputs')->nullable();
    
    // Validation status for strict schema enforcement
    $table->string('validation_status')->default('pending'); // pending, valid, invalid, error
    $table->text('validation_errors')->nullable();
    
    $table->integer('tokens_input')->default(0);
    $table->integer('tokens_output')->default(0);
    $table->decimal('cost_estimate', 10, 6)->default(0);
    $table->integer('duration_ms')->nullable();
    $table->timestamps();
    
    $table->index('research_run_id');
    $table->index(['account_id', 'run_type']);
});

// briefs - generated account summaries
Schema::create('briefs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('research_run_id')->constrained()->cascadeOnDelete();
    $table->foreignId('account_id')->constrained()->cascadeOnDelete();
    $table->foreignId('ai_run_id')->nullable()->constrained('ai_runs')->nullOnDelete();
    $table->integer('version')->default(1);
    $table->longText('content_md');
    $table->json('facts')->nullable(); // structured data extracted
    $table->timestamps();
    
    $table->index(['account_id', 'version']);
});

// outreach_assets - generated messages
Schema::create('outreach_assets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('account_id')->constrained()->cascadeOnDelete();
    $table->foreignId('research_run_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('playbook_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('ai_run_id')->nullable()->constrained('ai_runs')->nullOnDelete();
    $table->string('channel'); // linkedin, email
    $table->text('content');
    $table->string('status')->default('draft'); // draft, approved, sent
    $table->timestamp('sent_at')->nullable();
    $table->text('notes')->nullable();
    $table->string('outcome')->nullable(); // no_response, replied, meeting
    $table->timestamps();
    
    $table->index(['account_id', 'channel', 'status']);
});
```

### Enums (PHP 8.1+)

```php
// Sales pipeline (user-driven)
enum PipelineStage: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Replied = 'replied';
    case Meeting = 'meeting';
    case Proposal = 'proposal';
    case Won = 'won';
    case Lost = 'lost';
}

// Research status (system-driven)
enum ResearchStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case BlockedByBudget = 'blocked_by_budget';  // Daily spend limit hit
    case BlockedByLimit = 'blocked_by_limit';    // Daily account limit hit
}

enum ResearchRunStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}

enum SignalSeverity: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
}

// MVP signal types (text-grounded only)
enum SignalType: string
{
    case Content = 'content';       // Outdated content, copyright dates
    case Ux = 'ux';                 // Navigation issues, unclear journeys
    case Tech = 'tech';             // Technology mentions, stack indicators
    case Opportunity = 'opportunity'; // Hiring, initiatives, aligned services
    // Deferred to Phase 2:
    // case Accessibility = 'accessibility';  // DOM-based checks
    // case Performance = 'performance';      // Lighthouse metrics
}

enum OutreachStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Sent = 'sent';
}

enum ValidationStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Invalid = 'invalid';   // Schema validation failed
    case Error = 'error';       // AI call failed
}
```

This gives you _versioning_, auditability, and "regenerate without losing history".

---

## AI Architecture

### Prompt Chain (not one mega-prompt)

Rather than one mega prompt, use a **small chain** (more reliable, easier to debug, cheaper to iterate):

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Extractor  │ ──► │  Detector   │ ──► │   Brief     │ ──► │  Outreach   │
│   Prompt    │     │   Prompt    │     │  Generator  │     │   Writer    │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
     │                   │                    │                   │
     ▼                   ▼                    ▼                   ▼
 facts.json         signals[]             brief.md           dm + email
```

### Prompt Specifications

#### 1. Extractor Prompt
**Input:** Scraped page text (homepage, about, services)  
**Output:** Structured JSON

```json
{
  "company_name": "Example Ltd",
  "tagline": "...",
  "services": ["web development", "design"],
  "target_audience": ["SMEs", "charities"],
  "technologies_mentioned": ["WordPress", "Shopify"],
  "team_size_indicators": "10-50 based on about page",
  "tone": "professional, corporate",
  "navigation_items": ["Home", "About", "Services", "Blog", "Contact"],
  "last_updated_signals": "copyright 2023, blog posts from 2022"
}
```

#### 2. Signal Detector Prompt
**Input:** Extracted facts + heuristic rules  
**Output:** Array of signals

```json
[
  {
    "type": "accessibility",
    "severity": "high",
    "title": "Missing alt text on hero images",
    "description": "Homepage hero section contains 3 images without alt attributes",
    "evidence_url": "https://example.com/",
    "score_impact": 10
  }
]
```

#### 3. Brief Generator Prompt
**Input:** Facts + signals + ICP definition  
**Output:** Markdown brief

```markdown
# Account Brief: Example Ltd

## Overview
[2-3 sentence summary]

## Why Now
- [Signal-based timing reasons]

## Fit Assessment
- ICP Match: 8/10
- Key Alignment: [what matches your offer]

## Recommended Approach
[Suggested angle and entry point]
```

#### 4. Outreach Writer Prompt
**Input:** Brief + top 2 signals + playbook constraints  
**Output:** Channel-specific messages

```json
{
  "linkedin_dm": "Hi [Name], I noticed [specific signal]...",
  "email": {
    "subject": "Quick observation about [domain]",
    "body": "..."
  },
  "follow_up": "..."
}
```

### Model Selection Strategy

| Prompt | Model | Rationale |
|--------|-------|-----------|
| Extractor | gpt-4o-mini | Simple structured extraction, high volume |
| Signal Detector | gpt-4o-mini | Pattern matching, lower complexity |
| Brief Generator | gpt-4o | Needs good synthesis and writing |
| Outreach Writer | gpt-4o | Quality critical, client-facing output |

### Estimated Costs (per account)

| Prompt | Model | ~Tokens | ~Cost |
|--------|-------|---------|-------|
| Extractor | gpt-4o-mini | 2K in / 1K out | ~£0.01 |
| Signal Detector | gpt-4o-mini | 1.5K in / 0.5K out | ~£0.005 |
| Brief Generator | gpt-4o | 2K in / 1K out | ~£0.03 |
| Outreach Writer | gpt-4o | 1K in / 0.5K out | ~£0.02 |
| **Total per account** | | | **~£0.07** |

### AI Service Implementation

```php
// app/Services/AI/PromptService.php
class PromptService
{
    // Model mapping per prompt type
    private array $modelMap = [
        'extract' => 'gpt-4o-mini',
        'detect_signals' => 'gpt-4o-mini',
        'brief' => 'gpt-4o',
        'outreach' => 'gpt-4o',
    ];

    public function __construct(
        private OpenAIClient $client,
        private PromptRepository $prompts
    ) {}
    
    public function extract(Account $account): array
    {
        $prompt = $this->prompts->get('extractor', 'v1');
        $sources = $account->sources()->pluck('extracted_text');
        
        return $this->run($account, 'extract', $prompt, [
            'pages' => $sources,
        ]);
    }
    
    private function run(Account $account, string $type, Prompt $prompt, array $inputs): array
    {
        $start = microtime(true);
        $model = $this->modelMap[$type] ?? 'gpt-4o-mini';
        
        $response = $this->client->chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $prompt->systemPrompt()],
                ['role' => 'user', 'content' => $prompt->render($inputs)]
            ],
            'response_format' => ['type' => 'json_object'], // Structured output
        ]);
        
        $aiRun = AiRun::create([
            'account_id' => $account->id,
            'run_type' => $type,
            'model' => $model,
            'prompt_version' => $prompt->version,
            'inputs' => $inputs,
            'outputs' => $response->choices[0]->message->content,
            'tokens_input' => $response->usage->promptTokens,
            'tokens_output' => $response->usage->completionTokens,
            'cost_estimate' => $this->calculateCost($model, $response->usage),
            'duration_ms' => (microtime(true) - $start) * 1000,
        ]);
        
        return json_decode($response->choices[0]->message->content, true);
    }
    
    private function calculateCost(string $model, object $usage): float
    {
        // Prices per 1M tokens (as of 2024)
        $prices = [
            'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
            'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
        ];
        
        $p = $prices[$model] ?? $prices['gpt-4o-mini'];
        return (($usage->promptTokens * $p['input']) + 
                ($usage->completionTokens * $p['output'])) / 1_000_000;
    }
}
```

### Polite Crawling

| Constraint | Implementation |
|------------|----------------|
| robots.txt | Check and cache per domain (24h TTL), respect Disallow |
| Per-domain throttle | Max 1 request per 2 seconds per domain |
| Max page size | 5MB HTML limit, skip larger pages |
| Max text length | 100KB extracted text limit per page |
| User-Agent | Identify as HareAndTortoiseBot with contact URL |
| Timeout | 30 seconds per request |

```php
// app/Services/Crawler/PoliteCrawler.php
class PoliteCrawler
{
    public function canCrawl(string $url): bool
    {
        $domain = parse_url($url, PHP_URL_HOST);
        $robots = $this->getRobotsTxt($domain); // cached
        return $robots->isAllowed($url, 'HareAndTortoiseBot');
    }
    
    public function fetch(string $url): CrawlResult
    {
        $this->throttle(parse_url($url, PHP_URL_HOST));
        
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => config('crawler.user_agent')])
            ->get($url);
            
        if ($response->body() > 5 * 1024 * 1024) {
            throw new PageTooLargeException($url);
        }
        
        return new CrawlResult($response);
    }
}
```

### Rate Limiting & Cost Control

| Constraint | Implementation |
|------------|----------------|
| Max accounts/day | 50 (configurable, `blocked_by_limit` status when hit) |
| Max AI spend/day | £5 (configurable, `blocked_by_budget` status when hit) |
| Retry on failure | 3 attempts with exponential backoff |
| Queue priority | Hot leads processed first |

### Spend/Limit Behaviour

When limits are reached:
1. Running jobs complete normally
2. New jobs are blocked with appropriate status
3. Dashboard shows clear indicator of limit state
4. Limits reset at midnight UTC
5. Manual override available in settings

### Prompt Templates (Git-Versioned)

Prompts stored as files in `resources/prompts/` for version control:

```
resources/prompts/
├── extractor/
│   └── v1.txt
├── signal_detector/
│   └── v1.txt
├── brief_generator/
│   └── v1.txt
├── outreach_writer/
│   └── v1.txt
└── schemas/
    ├── extractor_output.json
    ├── signal_detector_output.json
    ├── brief_output.json
    └── outreach_output.json
```

```php
// Loading prompts
$prompt = file_get_contents(resource_path('prompts/extractor/v1.txt'));
$schema = json_decode(file_get_contents(resource_path('prompts/schemas/extractor_output.json')));
```

### AI Output Validation

All AI outputs validated against JSON schemas before use:

```php
// app/Services/AI/OutputValidator.php
class OutputValidator
{
    public function validate(string $runType, array $output): ValidationResult
    {
        $schema = $this->loadSchema($runType);
        $validator = new JsonSchema\Validator();
        $validator->validate($output, $schema);
        
        if (!$validator->isValid()) {
            return ValidationResult::invalid($validator->getErrors());
        }
        
        return ValidationResult::valid();
    }
}
```

Invalid outputs:
- Logged with `validation_status = 'invalid'`
- Research run continues with partial data where possible
- Account marked for manual review if critical prompts fail

---

## Scoring System

### Score Components (0-100 total)

| Component | Range | Description |
|-----------|-------|-------------|
| ICP Fit | 0-40 | How well they match your ideal customer profile |
| Signal Strength | 0-40 | Quality and quantity of detected issues |
| Reachability | 0-20 | Can you find the right contact? Clear contact info? |

### Signal Weights (configurable per ICP)

| Signal Type | Default Weight | Notes |
|-------------|----------------|-------|
| Accessibility red flags | +12 | Your core differentiator |
| UX inconsistency across pages | +10 | Shows need for design system |
| Outdated content/copyright | +8 | Suggests neglected site |
| Multiple subdomains, different styles | +10 | Integration opportunity |
| Careers page shows digital roles | +6 | Budget for digital |
| Performance issues (slow load) | +8 | Quick win opportunity |
| Services aligned with your offer | +10 | Natural fit |
| Tech stack you specialise in | +6 | Laravel, WordPress |
| Recent redesign (negative) | -10 | Unlikely to need work |

### Score Breakdown Storage

```json
{
  "total": 72,
  "components": {
    "icp_fit": 35,
    "signals": 25,
    "reachability": 12
  },
  "signals_detected": [
    {"type": "accessibility", "impact": 12, "reason": "Missing alt text"},
    {"type": "ux", "impact": 10, "reason": "Inconsistent navigation"},
    {"type": "content", "impact": 3, "reason": "Copyright 2022"}
  ],
  "calculated_at": "2026-02-02T10:30:00Z"
}
```

---

## UI/UX Design

> Reference: `.cursor/rules/accessibility-standards.mdc`, `.cursor/rules/frontend-development.mdc`

### Key Views (Livewire Components)

> **MVP Principle**: Table-first UI. Defer charts and fancy dashboards to Phase 2.

#### 1. Dashboard
- Status summary cards (counts, not charts)
- Limit/budget status indicator (clear warning if blocked)
- This week's hot leads table (top 20 by score)
- Recent research runs table with cost
- Quick actions: Import CSV, Run All Research

#### 2. Accounts Index
- Filterable table: pipeline_stage, research_status, sector, score range
- Bulk actions: Run Research, Change Stage, Export CSV
- Inline quick view (expand row to see brief preview)
- Keyboard navigable with visible focus indicators

#### 3. Account Detail
- Header: name, URL, score, pipeline stage selector, research status badge
- Tabs: Overview, Research History, Signals, Brief, Outreach
- Research history shows all runs with traceability
- Actions: Run Research, Generate Outreach, Edit

#### 4. Weekly Shortlist
- Top 20 accounts by score, filterable by stage
- One-click copy for outreach messages
- Mark as sent, log outcome
- **Export**: Download as CSV

#### 5. Exports (MVP)
- **Weekly Shortlist CSV**: account, score, signals summary, recommended angle
- **Outreach Bundle**: ZIP with per-account folders containing brief.md + dm.txt + email.txt

#### 6. Settings
- ICP management (CRUD)
- Playbook management (CRUD)
- API key display (masked)
- Daily limits configuration
- Current spend/usage display

### Accessibility Requirements (WCAG 2.2 AA)

| Requirement | Implementation |
|-------------|----------------|
| Semantic HTML | Use `<main>`, `<nav>`, `<section>`, `<article>` appropriately |
| Heading Hierarchy | Proper h1 → h2 → h3 structure, one h1 per page |
| Form Labels | All inputs have associated `<label>` elements |
| Alt Text | Meaningful alt text for all informational images |
| Colour Contrast | Minimum 4.5:1 for normal text, 3:1 for large text |
| Keyboard Navigation | All interactive elements keyboard accessible |
| Focus Indicators | Visible focus states on all focusable elements |
| ARIA | Live regions for dynamic content, proper roles |
| Skip Links | "Skip to main content" link for keyboard users |
| Error Messages | Associated with inputs, announced to screen readers |

### Frontend Standards

| Standard | Implementation |
|----------|----------------|
| CSS Methodology | BEM naming convention |
| Responsive | Mobile-first approach |
| Progressive Enhancement | Core functionality works without JS |
| JavaScript | ES6+ with proper error handling |
| Build | Vite for asset compilation |
| Animations | Respect `prefers-reduced-motion` |

### Design System
- Match Hare & Tortoise brand: slate/primary colour scheme
- Use existing Tailwind config from marketing site
- Component library in `resources/views/components/`
- Consistent spacing scale (Tailwind defaults)

---

## Testing Strategy

> **MVP Decision**: Testing deferred for initial internal tool. Structure documented here for future implementation.

### Quality Gates (MVP - Simplified)

| Check | Tool | Command | MVP Status |
|-------|------|---------|------------|
| Static Analysis | PHPStan | `./vendor/bin/phpstan analyse` | Active |
| Code Style | Laravel Pint | `./vendor/bin/pint` | Active |
| Build | Vite | `npm run build` | Active |
| Tests | Pest | `composer test` | Deferred |
| Coverage | Pest | `composer test -- --coverage` | Deferred |

### Future Testing (Post-MVP)

When adding tests, prioritise:

1. **AI Service** - Mock API calls, validate outputs
2. **Scoring Logic** - Unit tests for calculation accuracy
3. **CSV Import** - Validation and de-duplication
4. **Research Jobs** - Queue processing, error handling

```php
// Example test structure for later
// tests/Unit/ScoringServiceTest.php
it('calculates ICP fit correctly');
it('applies signal weights');
it('caps score at 100');
```

---

## Definition of Done (MVP - Simplified)

> **MVP Decision**: Streamlined for internal tool. Full checklist in `.cursor/rules/definition-of-done.mdc` for future reference.

### Code Quality (Required)
- [ ] PHPStan analysis passes
- [ ] Laravel Pint - Code style standards met
- [ ] `npm run build` - Production build succeeds

### Security (Required)
- [ ] Input validation via Form Request classes
- [ ] No raw HTML output - Blade escaping used
- [ ] CSRF protection enabled
- [ ] No credentials in code or logs

### Accessibility (Best Effort)
- [ ] Semantic HTML structure
- [ ] Form labels associated with inputs
- [ ] Keyboard navigation works for main flows

### Deferred for Post-MVP
- Automated testing (Pest)
- 80% code coverage requirement
- Cross-browser testing
- Comprehensive WCAG 2.2 AA audit
- CHANGELOG updates
- Detailed PR descriptions

---

## Safety Protocols

> Reference: `.cursor/rules/safety-protocols.mdc`

### Database & Migration Safety
- **NEVER** run destructive migrations without explicit approval
- **ALWAYS** include `down()` method for rollbacks
- **REQUIRE** staging environment testing first
- **BACKUP** database before any data modification

### Pre-Deployment Checklist
- [ ] All tests passing
- [ ] Code review completed
- [ ] Security scan passed
- [ ] Staging environment tested
- [ ] Rollback plan documented
- [ ] Database backup confirmed

### Rollback Procedures
```bash
# Revert last migration
php artisan migrate:rollback --step=1

# Restore from backup (if needed)
mysql -u username -p database_name < backup.sql

# Clear caches
php artisan cache:clear && php artisan config:clear
```

---

## Local Development (DDEV)

### DDEV Configuration

```yaml
# .ddev/config.yaml
name: prospect-portal
type: laravel
docroot: public
php_version: "8.3"
database:
  type: mysql
  version: "8.0"
webserver_type: nginx-fpm
nodejs_version: "20"

# Additional services
hooks:
  post-start:
    - exec: composer install
    - exec: npm install
```

### Additional Services

```yaml
# .ddev/docker-compose.redis.yaml
services:
  redis:
    container_name: ddev-${DDEV_SITENAME}-redis
    image: redis:7-alpine
    restart: "no"
    labels:
      com.ddev.site-name: ${DDEV_SITENAME}
      com.ddev.approot: ${DDEV_APPROOT}
    expose:
      - "6379"
    volumes:
      - redis-data:/data

volumes:
  redis-data:
```

### Local Development Commands

```bash
# Start environment
ddev start

# Run artisan commands
ddev artisan migrate
ddev artisan queue:work

# Run npm commands
ddev npm run dev

# Access services
ddev describe          # Show URLs and ports
ddev ssh               # Shell into container
ddev launch            # Open in browser

# Database access
ddev mysql             # MySQL CLI
ddev sequelace         # Open in Sequel Ace (macOS)
```

### Environment Setup

```bash
# .ddev/.env (DDEV-specific overrides)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=db
DB_USERNAME=db
DB_PASSWORD=db

REDIS_HOST=redis
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

---

## Deployment & Operations

### Infrastructure (Laravel Cloud)

```yaml
# laravel-cloud.yaml equivalent
environment: production
php: 8.3
queue:
  driver: redis
  workers: 2
scheduler: enabled
storage:
  disk: local  # MVP, migrate to S3 later
```

### Environment Variables

```env
# .env.example additions
APP_ENV=production
APP_DEBUG=false

# OpenAI Configuration
OPENAI_API_KEY=sk-...
OPENAI_MODEL_FAST=gpt-4o-mini
OPENAI_MODEL_QUALITY=gpt-4o

# Rate Limits
RESEARCH_DAILY_LIMIT=50
AI_DAILY_SPEND_LIMIT=5.00

# Scraping
SCRAPE_TIMEOUT=30
SCRAPE_USER_AGENT="HareAndTortoiseBot/1.0 (+https://hareandtortoise.agency)"

# Security Headers
CSP_ENABLED=true
HSTS_ENABLED=true

# Session Security
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
```

### Monitoring
- Laravel Telescope (local dev)
- Laravel Cloud monitoring (production)
- Sentry for error tracking
- Daily email digest: accounts processed, spend, errors
- Performance alerts: response time > 500ms, error rate > 0.5%

### Backup Strategy
- Database: Daily automated backups (Laravel Cloud)
- Retention: 30 days
- Scraped HTML: Expendable (can re-fetch)
- Test restore procedure monthly

---

## Documentation Standards

> Reference: `.cursor/rules/documentation-standards.mdc`

### Required Documentation

| Document | Location | Purpose |
|----------|----------|---------|
| README.md | Root | Setup, installation, configuration |
| CHANGELOG.md | Root | Version history, changes |
| API Documentation | `docs/api.md` | Endpoint reference (if API exposed) |
| Architecture Decisions | `docs/adr/` | ADR records for key decisions |

### Code Documentation

```php
/**
 * Calculate lead score for an account based on ICP fit and detected signals
 * 
 * Scoring is transparent: breakdown stored as JSON for explainability.
 * Score capped at 100, minimum 0.
 * 
 * @param Account $account The account to score
 * @param Icp $icp The ideal customer profile to score against
 * @return int The calculated score (0-100)
 * 
 * @example
 * $score = $this->scoringService->calculate($account, $icp);
 */
public function calculate(Account $account, Icp $icp): int
```

### Architecture Decision Records (ADR)

For significant decisions, create an ADR in `docs/adr/`:

```markdown
# ADR-001: Use Livewire over Inertia

## Status
Accepted

## Context
Need to choose frontend approach for the portal.

## Decision
Use Livewire 3 + Alpine.js for all interactive components.

## Consequences
- Faster development with less JavaScript
- Easier accessibility implementation
- Trade-off: Less suitable for highly interactive SPAs
```

---

## Project Phases

### Phase 1: MVP (Target: 2-3 weeks)

#### Setup & Foundation
- [ ] DDEV configuration (PHP 8.3, MySQL 8, Redis, Node 20)
- [ ] Project setup (Laravel 12, Breeze, Livewire, Tailwind)
- [ ] Configure PHPStan, Pint for code quality
- [ ] Database migrations (including research_runs table)
- [ ] Enums for pipeline_stage, research_status, etc.
- [ ] Base Livewire components and layout

#### Research Infrastructure
- [ ] Polite crawler (robots.txt, throttling, size limits)
- [ ] Disk storage for HTML/text snapshots
- [ ] Research run job with full traceability
- [ ] OpenAI service with model mixing
- [ ] JSON schema validation for AI outputs
- [ ] Prompt templates in `resources/prompts/`

#### Core Features
- [ ] Account CRUD + CSV import with validation
- [ ] Separated pipeline_stage and research_status
- [ ] ICP and Playbook management
- [ ] Scoring system with transparent breakdown
- [ ] Rate limiting and budget control
- [ ] Dashboard (table-first, status cards)
- [ ] Weekly shortlist with exports
- [ ] Outreach viewing and manual tracking

#### Wrap-up
- [ ] Basic security review (Form Requests, CSRF, escaping)
- [ ] README with setup instructions
- [ ] Deploy to Laravel Cloud

### Phase 2: Refinement (Post-MVP)

#### Features
- [ ] Contact management per account
- [ ] Follow-up sequence tracking
- [ ] Outcome analytics (which signals convert?)
- [ ] Playbook performance comparison
- [ ] Scheduled weekly research runs (Laravel scheduler)
- [ ] Email notifications for hot leads

#### Quality (Deferred from MVP)
- [ ] Add Pest test suite for critical paths
- [ ] Performance optimization (caching, query optimization)
- [ ] WCAG 2.2 AA accessibility audit
- [ ] Cross-browser testing

### Phase 3: Scale (Future)
- [ ] Multi-user / team support
- [ ] Third-party enrichment (Clearbit, Apollo)
- [ ] LinkedIn integration (compliant)
- [ ] Advanced analytics dashboard
- [ ] API for external integrations
- [ ] Comprehensive browser testing (Dusk suite)

---

## Success Metrics

### Business Metrics (Primary)

| Metric | Target | Measurement |
|--------|--------|-------------|
| Weekly shortlist generated | 20 accounts | System count |
| Time to first outreach | < 30 min/account | Manual tracking |
| Research accuracy | > 80% useful signals | Qualitative review |
| Cost per account | < £0.50 | AI run logs |
| Reply rate improvement | Baseline + 20% | Outcome tracking |

### Quality Metrics (MVP - Simplified)

| Metric | Target | Tool |
|--------|--------|------|
| PHPStan errors | 0 | PHPStan |
| Pint errors | 0 | Laravel Pint |
| Build success | 100% | npm run build |

### Performance Metrics (Monitor Informally)

| Metric | Target | Measurement |
|--------|--------|-------------|
| Page load time | < 3s | Browser dev tools |
| Obvious N+1 queries | 0 | Laravel Debugbar |

---

## Appendix: Cursor Rules Reference

The following cursor rules apply to this project:

| Rule File | Applies To | Key Requirements |
|-----------|------------|------------------|
| `definition-of-done.mdc` | All code | 80% coverage, accessibility, security |
| `php-laravel-standards.mdc` | PHP files | PSR-12, Form Requests, API Resources |
| `security-first-approach.mdc` | All code | OWASP Top 10, input validation |
| `accessibility-standards.mdc` | Frontend | WCAG 2.2 AA, keyboard nav, ARIA |
| `testing-requirements.mdc` | Tests | Pest, 80% coverage, mocking |
| `safety-protocols.mdc` | Migrations, deploy | Rollbacks, backups, staging |
| `frontend-development.mdc` | Frontend | BEM, responsive, progressive enhancement |
| `performance-optimization.mdc` | All code | Caching, eager loading, indexing |
| `documentation-standards.mdc` | Docs | PHPDoc, ADRs, CHANGELOG |
| `general-development.mdc` | All files | Code review, communication |