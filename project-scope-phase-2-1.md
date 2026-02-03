Below are the improvements I'd make before you lock it.

**1) Make lead_source_runs non-optional (you'll want it immediately)**

You've got lead_sources.last_run_\* _and_ optional lead_source_runs. In practice, you'll need history when:

- a provider returns junk for 3 days
- your blocklist is too aggressive
- you hit rate limits
- you want to compare query packs

**Recommendation:** make lead_source_runs required for Phase 2a.  
Then lead_sources.last_run_\* is just a denormalised "latest" snapshot for quick UI display.

**Add columns to lead_source_runs:**

- trigger enum: manual|scheduled
- config_snapshot JSON (store the config used at run time, so edits don't change history)
- queries_executed int
- throttled_count int
- provider string (bing/google_cse)

This makes debugging painless.

**2) Clarify domain normalisation rules (and what you do with subdomains)**

Your current normaliser "strip www, trailing slashes, path" is good but incomplete. You need a decision on:

**A) Should foo.example.com become example.com or stay foo.example.com?**

For prospecting, usually **collapse to registrable domain** (example.com) so:

- you don't create duplicates for www, app, blog, jobs
- you get a single "account" per org

**Recommendation:** normalise to the **registrable domain** (eTLD+1) where possible.  
Edge case: some orgs use something.gov.uk / something.sch.uk / something.ac.uk patterns, so eTLD+1 logic needs a public suffix list.

**MVP compromise if you don't want PSL complexity yet:**

- If host starts with <www>. → strip it.
- If host is app. blog. careers. jobs. → strip those common prefixes.
- Otherwise keep host as-is.

But I'd still prefer doing it properly, even if you use a small library.

**B) Canonical URL storage**

Keep:

- accounts.domain (normalised unique key)
- accounts.url (canonical "best guess" base URL, e.g. <https://example.com>)

When you discover a new URL for an existing domain, you should **optionally update** accounts.url _only if_ it's "better" (e.g. https > http, root > deep path).

Add a short "URL selection rule" in scope.

**3) Add a deterministic "approve-before-import" workflow**

You've got a config flag; good. But implementation will be cleaner if you define statuses.

**Suggestion: two-phase ingest**

- Run creates discovery_candidates rows (domain, url, title, source metadata, reason blocked)
- If approve disabled → auto-approve and promote candidates into accounts
- If approve enabled → user selects candidates to import

**Why:** it gives you preview, explainability, and you can show _why_ something was blocked/skipped.

**MVP schema (light):** discovery_candidates

- lead_source_run_id
- domain
- url
- title/snippet
- status: new|approved|rejected|blocked|duplicate
- reason text
- indexes on domain + run

You can still skip this and show a sample list from memory, but the moment you want "approve", you'll reinvent this table anyway.

**4) Provider connector contract: define what "result" looks like**

Your interface is good; add a strict DTO shape:

**DiscoveryRunResult must include:**

- provider
- items\[\] where each item has:
  - url
  - title (optional)
  - snippet (optional)
  - position (int)
  - query (string) (if query pack)
- errors\[\] with query-level failures
- rate_limit_hit bool

This makes ingestion deterministic and keeps connector-specific mess out of your job.

**5) Explicitly define "blocklist" matching semantics**

Right now it's "domains / patterns", but implementation can vary wildly.

Define:

- Block by **exact domain** (facebook.com)
- Block by **suffix** (\*.facebook.com)
- Block by **contains** (rarely needed)
- Block by **regex** (optional)

**Recommendation for MVP:** exact + suffix only. Regex later.

Also add a **default blocklist seed** in scope (high-value):

- social networks, directories, gov portals, Wikipedia, map sites, app stores, PDF hosts, etc.

**6) Add basic connector caching to cut costs + noise**

Search APIs will repeat results. Add:

- Cache query results for 24h (per query string + provider)
- Store in lead_source_runs.config_snapshot + optionally query_hash

Even a simple cache reduces API spend dramatically.

**7) Make "classification" slightly more concrete (still non-AI)**

You say "optional rule-based filter". Great - specify 2-3 rules you _will_ implement so it doesn't get ignored.

**Suggested MVP rules:**

- Reject domains with no dot / invalid TLD
- Reject obvious file hosts / CDN domains (from blocklist)
- Reject URLs that are not http(s)
- (Optional) If domain resolves to HTTP 4xx/5xx on HEAD request → mark candidate "unreachable" (don't create account yet)

The last one is huge for reducing junk accounts.

**8) Research auto-trigger: define what happens when daily limits are hit**

You say limits apply. Good. But the UX needs to show what happened.

Add:

- If research job can't run due to limits → set research_status=queued_blocked (or similar) and store reason
- Provide a "Run research now" button that overrides only when you explicitly want

Otherwise you'll wonder why "discovery worked but nothing got researched".

**9) Scheduling: specify the schedule windows**

"Daily/weekly at configured time" is good but vague.

MVP: hardcode something sensible and make it configurable later:

- Daily at 07:00 Europe/London
- Weekly on Monday at 07:30

(Or whichever fits your routine.)

This avoids decision paralysis during build.

**10) Small terminology tweak: "Phase 2b" used twice**

You have:

- "Lookalike expansion (Phase 2b)"
- "Directory / dataset ingestion (Phase 2b)"

Rename to:

- 2b1 Lookalikes
- 2b2 Registries/Directories  
    or just make them separate rows in the phasing table.

**11) Definition of Done: scope the 80% coverage requirement**

Please remove tests from this MVP but keep in mind we will need them in the future so framework should exist.

**Minimal "patch notes" you can paste into the scope**

If you want a neat addendum, here's the exact text to append:

**Final clarifications (Phase 2a)**

- lead_source_runs is **required** (not optional) and stores trigger, provider, config_snapshot, throttled_count, and query-level errors.
- Domain normalisation uses a consistent rule (prefer registrable domain / eTLD+1). Canonical accounts.url is updated only if the new URL is "better" (https, root).
- Approve-before-import uses persisted discovery_candidates linked to lead_source_run_id with statuses (new|approved|rejected|blocked|duplicate|unreachable).
- Blocklist matching is defined (exact + suffix matching for MVP).
- Query results are cached for 24h to reduce API costs and repetition.
- If research can't run due to daily limits/budget, accounts are marked research_status=queued_blocked with a reason surfaced in UI.

**What I'd build first inside Phase 2a (order matters)**

- lead_sources, lead_source_runs, discovery_candidates migrations
- Provider connector + DiscoveryRunResult DTO
- Ingestion job (normalise/dedupe/blocklist/candidates)
- Candidate approval UI (only if flag enabled)
- Promotion job (candidates → accounts + queue research)
- Scheduler + basic observability UI

That sequence keeps the engine clean and avoids rework.