# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Changed – Discovery default provider: SerpAPI (Bing retired)

- **Default discovery provider** is now **SerpAPI** (Google results via SerpAPI). Bing Search APIs (including v7) were retired on 11 August 2025; new Azure resources are no longer available.
- **SerpApiSearchConnector** added: implements `DiscoveryConnector`, calls SerpAPI Google Search (`engine=google`), maps `organic_results` to discovery items, 24h cache and rate throttling. Config: `SERPAPI_API_KEY` in `.env`, `config('discovery.default_provider')` defaults to `serpapi`.
- **BingSearchConnector** retained for legacy; set `DISCOVERY_DEFAULT_PROVIDER=bing` and `BING_SEARCH_API_KEY` if you still have a pre-retirement key.
- **README** and lead source Create/Edit default to `SERPAPI_API_KEY` for new lead sources.

### Added – Phase 2a: Account Discovery (2026-02-03)

- **Lead Sources**: CRUD for lead sources (search query packs). Create, edit, delete lead sources with name, seed queries, cadence (manual/daily/weekly), and status (enabled/disabled).
- **Discovery pipeline**: Ingestion job runs connector (Bing Web Search API), normalises domains (eTLD+1 / strip www + common prefixes), dedupes, applies blocklist (exact + suffix), creates discovery candidates. Optional approve-before-import: candidates can be approved/rejected then promoted to accounts.
- **Discovery candidates**: Table and UI to list candidates per run with status (new, approved, rejected, blocked, duplicate, unreachable) and reason. When approve-before-import is disabled, candidates are auto-approved and promoted.
- **Promotion job**: Approved candidates are turned into accounts (or update existing account URL if better). Research is queued for each new account via existing `RunAccountResearch` with `triggeredBy: 'discovery'`.
- **Bing connector**: `BingSearchConnector` implements `DiscoveryConnector`; reads queries from lead source config, calls Bing Web Search API v7, respects rate limit; query results cached 24h per query + provider.
- **Config**: `config/discovery.php` with default_provider, rate_limit_per_minute, blocklist_domains (default seed: social, directories, gov, Wikipedia, map sites, app stores, PDF hosts), approve_before_import. API key via `BING_SEARCH_API_KEY` in `.env`.
- **Scheduler**: Daily at 07:00 Europe/London and weekly on Monday at 07:30 Europe/London for lead sources with cadence daily/weekly.
- **Research status QueuedBlocked**: When research cannot run due to daily limit or budget, accounts are set to `research_status=queued_blocked` with reason (e.g. "Daily limit reached"). UI shows reason and "Run research now" to override.
- **Data model**: `lead_sources`, `lead_source_runs`, `discovery_candidates` tables; account provenance (`lead_source_id`, `discovered_at`, `discovery_metadata`, `research_blocked_reason`). Enums: LeadSourceType, LeadSourceCadence, LeadSourceStatus, LeadSourceRunTrigger, LeadSourceRunStatus, DiscoveryCandidateStatus; ResearchStatus::QueuedBlocked.
- **Navigation**: "Lead Sources" link in main nav; routes for lead-sources index, create, edit, and lead-source-runs/{run}/candidates.
