# Hare & Tortoise Prospect Portal — Strategic Overview

**Prepared:** February 2026
**Author:** AI Strategy Analysis
**Audience:** Non-technical, commercially savvy mentor / advisor

---

## 1. Executive Summary

**Hare & Tortoise Prospect Portal is an AI-powered prospect research and outreach platform that automates the entire B2B sales research pipeline — from finding potential clients, to researching them, scoring them, and generating personalised outreach messages — in minutes rather than hours.**

### Core Value Proposition

The platform replaces the manual, time-intensive work of prospecting: Googling for leads, visiting their websites, figuring out what's wrong with their digital presence, writing up research notes, and crafting personalised outreach. It compresses what would take an SDR 30–60 minutes per prospect into a largely automated pipeline that costs pennies per account.

### Why It Matters Commercially

Every B2B service business — agencies, consultancies, SaaS companies — burns significant time on prospect research. The people doing this work are expensive (founders, senior staff, SDRs), the output is inconsistent, and it doesn't scale. This platform turns research into a repeatable, scalable system.

### Why It Could Scale

The architecture is modular and pipeline-driven. The AI components are abstracted behind prompt templates with versioning. The discovery, research, scoring, and outreach stages are decoupled. This means each stage can be improved, swapped, or scaled independently. The underlying problem — "find me good prospects and help me reach them" — is universal across B2B services.

---

## 2. The Problem It Solves

### The Pain Point

B2B service businesses (especially digital agencies) need a steady pipeline of qualified prospects. The process of finding and researching those prospects is:

- **Manual**: Someone has to Google, browse websites, take notes, and assess fit.
- **Slow**: A thorough prospect brief takes 30–60 minutes per company.
- **Inconsistent**: Quality varies depending on who does the research and when.
- **Expensive**: Founders or senior staff often do this themselves because junior hires lack context.
- **Unscalable**: Doing 10 prospects a day is exhausting; doing 50 is impossible manually.

### Who Experiences This Pain

- Agency founders who prospect alongside client delivery
- Sales Development Representatives (SDRs) doing outbound at scale
- Growth teams at consultancies and B2B SaaS companies
- Solo consultants who have no dedicated sales support

### Current Alternatives

| Solution | Limitation |
|---|---|
| **Manual research** (Google + browsing) | Slow, inconsistent, doesn't scale |
| **Generic CRMs** (HubSpot, Pipedrive) | Manage contacts, don't generate research or outreach |
| **Lead databases** (Apollo, ZoomInfo) | Provide contact data, not strategic research or signals |
| **Scraping tools** (Phantombuster, Clay) | Require technical setup; output raw data, not insight |
| **AI writing tools** (ChatGPT, Jasper) | Generic; require manual context-gathering first |

### Why Current Solutions Are Inefficient

Existing tools solve fragments of the problem. You need one tool to find leads, another to research them, another to score them, and still have to write outreach yourself. No current tool chains together: **discovery → crawling → signal detection → scoring → brief generation → personalised outreach** into a single automated pipeline. This platform does.

---

## 3. What The Platform Does

### End-to-End Pipeline

The platform operates as a five-stage automated pipeline:

```
Discovery → Research → Scoring → Brief Generation → Outreach Generation
```

### Stage 1: Data Discovery

- Users define **Lead Sources** — essentially search query packs (e.g., "UK charity websites needing accessibility help").
- The platform generates optimised search queries using AI and executes them via Google (SerpAPI).
- Results are normalised, deduplicated, checked against a blocklist, and surfaced as **Discovery Candidates**.
- Users can review and approve/reject candidates before they enter the pipeline (or auto-approve).
- Runs can be scheduled daily or weekly on a cadence.

**Input:** A description of who you want to find.
**Output:** A filtered list of real company domains with initial fit assessment.

### Stage 2: AI-Powered Research

- For each approved account, the platform dispatches a **Research Run**.
- A polite web crawler (respects robots.txt, rate limits) fetches key pages: homepage, about, services, contact, careers.
- Page content is stored as snapshots for traceability.
- An AI **Extractor** analyses the pages and pulls structured data: company name, sector, size band, services offered, technologies used, location.

**Input:** A company domain.
**Output:** Structured company profile data extracted from their own website.

### Stage 3: Signal Detection

- A dedicated AI **Signal Detector** analyses the crawled content looking for actionable signals:
  - **Content signals**: Outdated blog, stale content, copyright years behind.
  - **UX signals**: Poor navigation, accessibility issues, mobile problems.
  - **Tech signals**: Outdated frameworks, technical debt indicators.
  - **Opportunity signals**: Hiring activity, growth indicators, funding mentions.
- Each signal includes severity (high/medium/low), evidence snippets, and a score impact.

**Input:** Crawled website content.
**Output:** A list of specific, evidence-backed signals that create a reason to reach out.

### Stage 4: Scoring & Prioritisation

- Each account receives a **Lead Score** (0–100) composed of three dimensions:
  - **ICP Fit (0–40):** How well the company matches the Ideal Customer Profile (sector, size, location).
  - **Signal Strength (0–40):** How many and how severe the detected signals are.
  - **Reachability (0–20):** Whether the research completed, briefs were generated, and the company is contactable.
- Users define **Ideal Customer Profiles (ICPs)** with sector preferences, size bands, signal weights, and scoring weights.

**Input:** Extracted data + detected signals + ICP definition.
**Output:** A prioritised, scored list of prospects — hottest leads at the top.

### Stage 5: Brief & Outreach Generation

- An AI **Brief Generator** produces a comprehensive research brief for each account: company overview, key facts, identified opportunities, recommended approach, and talking points.
- An AI **Outreach Writer** generates personalised messages across two channels:
  - **LinkedIn DM** (under 300 characters, conversational).
  - **Email** (subject line + body, professional but personalised).
- Outreach is generated based on **Playbooks** — configurable outreach strategies with different angles (e.g., "Accessibility & UX" vs. "Modernisation").

**Input:** Research brief + signal data + playbook template.
**Output:** Ready-to-send personalised messages grounded in real evidence from the prospect's website.

### Additional Features

- **Dashboard**: At-a-glance stats — total accounts, daily research count, AI spend tracking, pipeline distribution, hot leads table, and recent research activity.
- **Pipeline Management**: Accounts move through stages: New → Contacted → Replied → Meeting → Proposal → Won / Lost.
- **Export**: CSV shortlist of top 50 scored accounts; ZIP bundle of briefs and outreach assets.
- **Cost Controls**: Hard daily limits on research runs (default: 50/day) and AI spend (default: $5/day) to prevent runaway costs.
- **AI Audit Trail**: Every AI call is logged with model used, prompt version, token counts, cost estimate, duration, and validation status.

### What Manual Work It Eliminates

| Before | After |
|---|---|
| Googling for prospects | Automated discovery via search queries |
| Browsing prospect websites | Automated crawling and extraction |
| Taking research notes | AI-generated structured briefs |
| Assessing prospect fit | Automated ICP scoring |
| Writing personalised outreach | AI-generated messages with real evidence |
| Tracking in spreadsheets | Built-in pipeline and dashboard |

---

## 4. Target Customer Profile

### Primary User: Digital Agency Founder / Business Development Lead

**[Assumption]** Based on the seeded data (Hare & Tortoise agency), ICP definitions (UK SMEs, accessibility/UX focus), and playbook angles, the platform was built for a specific use case: a UK-based digital agency prospecting for SMEs with website problems.

- **Role**: Agency founder, BD lead, or senior consultant who currently does their own prospecting.
- **Pain**: Spending 5–10 hours/week on manual research that could be spent on billable work.
- **Value**: Time savings of 80%+ on prospect research; higher quality and consistency.

### Secondary User: SDR / Outbound Sales Team

- Teams doing volume outbound who need personalised research at scale.
- Currently limited by how many prospects they can manually research per day.

### Ideal Company Size

- **Sweet spot**: 1–50 person B2B service businesses (agencies, consultancies, specialist firms).
- **Why**: Large enough to need a pipeline, small enough that they can't afford dedicated research staff.
- **[Assumption]** Larger organisations (50–200) with dedicated BD teams could also benefit, using this to augment SDR output.

### Ideal Sectors

- Digital agencies (web design, development, accessibility, UX)
- Marketing agencies (SEO, content, PPC)
- IT consultancies
- Management consultancies
- Any B2B service business that prospects via outbound

---

## 5. Differentiation

### What Makes This Different From Standard Lead Tools

| Traditional Lead Tools | This Platform |
|---|---|
| Give you a list of contacts | Gives you researched, scored, ready-to-contact prospects |
| Provide generic company data | Extracts specific insights from the prospect's actual website |
| Require you to write outreach | Generates personalised outreach grounded in real signals |
| Score based on firmographics only | Scores based on ICP fit + detected signals + reachability |
| Operate on static databases | Researches live website data in real-time |

### Where AI Adds Real Leverage

AI is not a bolted-on feature here — it is integral to every stage:

1. **Query generation**: AI creates optimised search queries from natural-language descriptions.
2. **ICP fit assessment**: AI evaluates whether discovery candidates match the ideal profile.
3. **Data extraction**: AI structures raw website content into usable company profiles.
4. **Signal detection**: AI identifies specific, evidence-backed opportunities from website analysis.
5. **Brief generation**: AI synthesises research into actionable sales briefs.
6. **Outreach writing**: AI creates personalised messages that reference real findings.

This is six distinct AI applications in a single pipeline, each with its own prompt, schema validation, and cost tracking.

### Platform Classification

This is best described as an **AI-powered prospecting engine** — not a CRM, not a lead database, and not a generic AI writing tool. It sits in a unique space:

- **Research engine** that generates original insight (not just data lookup).
- **Workflow tool** that automates a multi-step process end to end.
- **AI prospecting assistant** that produces ready-to-use sales materials.

The closest comparable positioning would be a verticalised, research-first alternative to tools like Clay or Apollo — but with deeper AI analysis and integrated outreach generation.

---

## 6. Current Architecture Overview (High-Level)

### Core Stack

| Layer | Technology | Purpose |
|---|---|---|
| Backend | Laravel 12 (PHP 8.2) | Application framework |
| Frontend | Livewire 3 + Tailwind CSS | Reactive UI without JavaScript framework |
| Database | MySQL 8 | Persistent storage |
| Queue | Redis | Background job processing |
| Cache | Redis | Performance caching |
| AI | OpenAI (GPT-4o, GPT-4o-mini) | All AI capabilities |
| Discovery | SerpAPI (Google Search) | Lead discovery |
| Local Dev | DDEV | Containerised development environment |

### AI Architecture

- **Prompt versioning**: All prompts stored as versioned text files (`v1.txt`) with separate JSON schemas for output validation.
- **Two-tier model strategy**: Fast model (GPT-4o-mini) for high-volume extraction and scoring; quality model (GPT-4o) for briefs and outreach.
- **Cost tracking**: Every AI call logged with token counts, cost estimates, and validation status.
- **Daily budget cap**: Hard limit prevents runaway spend (default $5/day).

### Data Flow

```
Lead Source → Discovery Run → Candidates → Accounts
                                              ↓
                                     Research Run
                                    ↙     ↓      ↘
                              Crawling  Extraction  Signal Detection
                                              ↓
                                     Brief Generation
                                              ↓
                                    Outreach Generation
                                              ↓
                                     Score Calculation
```

### Scalability Considerations

**Strengths:**
- Pipeline stages are decoupled and run as background jobs via Redis queues.
- Crawling is polite (rate-limited, robots.txt-aware) which prevents IP blocking.
- AI costs are capped with daily budget limits.
- Prompt templates are versioned, enabling A/B testing and iteration.

**Bottlenecks / Fragile Areas:**
- **Single AI provider**: Entirely dependent on OpenAI. No fallback if the API is down or pricing changes.
- **Single discovery provider**: SerpAPI is the only active search connector (Bing connector exists but is retired).
- **No horizontal scaling**: Queue processing is Redis-based but there's no multi-worker or distributed architecture documented.
- **No API layer**: The application has no external API — it's a monolithic web app. This limits integration possibilities.
- **Test coverage gaps**: Existing tests cover authentication only, not core business logic (research pipeline, scoring, AI integration).

---

## 7. Commercialisation Pathways

### Packaging Options

#### Option A: Standalone SaaS (Recommended for Scale)

Package as a self-service SaaS product where users sign up, define their ICP, set up lead sources, and let the platform find and research prospects automatically.

- **MVP Tier**: Discovery + Research + Scoring + Brief export. Limited to 50 accounts/month.
- **Pro Tier**: Full pipeline including outreach generation, playbooks, scheduled discovery, export bundles.
- **Agency Tier**: Multi-user, white-label reports, higher volume limits, custom ICPs.

#### Option B: Agency Internal Tool (Current State)

Keep it as a proprietary competitive advantage for Hare & Tortoise's own business development. No revenue from the tool itself, but significant time savings.

#### Option C: White-Labelled Product

License the platform to other agencies under their own branding. Agencies get a research tool; you get recurring licence revenue.

#### Option D: API Product

Expose the research and signal detection pipeline as an API that other platforms (CRMs, outreach tools) can integrate with.

### Pricing Models

| Model | Structure | Best For |
|---|---|---|
| **Subscription** | £49/£99/£249 per month by tier | Standalone SaaS |
| **Usage-based** | £0.50–£1.00 per researched account | API product, high-volume users |
| **Seat-based** | Per-user pricing within tiers | Agency teams |
| **Hybrid** | Base subscription + usage overage | Balancing predictability with scale |

**[Assumption]** Based on the value delivered (30–60 min saved per prospect), pricing of £0.50–£2.00 per researched account would represent strong ROI for users. A monthly subscription at £99 covering ~200 researched accounts would be competitive.

### Recommended Path

**Start as a standalone SaaS (Option A)** with a clear free trial or freemium entry point. The product is already built for a single-user workflow and could be extended to multi-tenant with moderate effort. The white-label and API paths become viable at scale but require significant additional investment.

---

## 8. Risks & Constraints

### Technical Limitations

- **No multi-tenancy**: Currently user-scoped but not architected for true multi-tenant SaaS (no organisation model, no billing, no subscription management).
- **No external API**: Cannot be integrated into other tools without building an API layer.
- **Limited test coverage**: Core business logic (the entire research pipeline) has no automated tests. This is a significant risk for reliability and refactoring.
- **No CI/CD pipeline documented**: No evidence of automated deployment or continuous integration.

### Data Reliability Risks

- **Website crawling is inherently fragile**: Sites change structure, block crawlers, or return inconsistent content.
- **AI extraction accuracy is not guaranteed**: LLMs can hallucinate or misinterpret website content. Output validation exists but catches structural errors, not factual ones.
- **Discovery quality depends on search queries**: Poor queries produce poor candidates. The AI query generator helps but isn't foolproof.

### AI Cost Risks

- **OpenAI pricing changes**: The platform's unit economics are directly tied to OpenAI's per-token pricing. A price increase or model deprecation would impact margins.
- **Cost per account**: Each full research run involves 4+ AI calls (extraction, signals, brief, outreach). At current GPT-4o pricing, this is manageable (~$0.05–0.15 per account) but could escalate with volume or model upgrades.
- **Daily budget cap is a safeguard, not a solution**: The $5/day cap prevents runaway spend but also limits throughput. For SaaS, per-tenant cost management would be needed.

### Platform Dependency Risks

- **100% OpenAI-dependent**: No fallback to Anthropic, Google, or open-source models.
- **SerpAPI dependency**: Discovery relies on a single third-party search API.
- **No offline capability**: Every research run requires live API calls.

### Compliance Concerns

- **GDPR**: The platform crawls and stores website content. While this is publicly available information, storing and processing it at scale may raise data protection questions, particularly if extended to store personal data (e.g., contact names, email addresses).
- **Web scraping legality**: Crawling websites for commercial intelligence operates in a grey area. The "polite crawler" approach (respecting robots.txt) mitigates but doesn't eliminate legal risk.
- **AI-generated outreach**: Regulations around automated outreach (especially email) vary by jurisdiction. Users would need to comply with CAN-SPAM, PECR, and similar regulations.

### Maintenance Burden

- **Prompt engineering**: AI prompts need ongoing tuning as models evolve and edge cases emerge.
- **Website parsing**: Crawling and extraction logic will need maintenance as web standards and site structures change.
- **Dependency updates**: Laravel 12 is current, but the OpenAI PHP client and SerpAPI integration will need ongoing attention.

---

## 9. 90-Day Scale Plan

### Phase 1: Stabilise (Weeks 1–3)

**Goal:** Make the existing platform reliable and testable.

- Write automated tests for the core research pipeline (crawling, extraction, signal detection, scoring, brief generation, outreach generation).
- Achieve 80%+ test coverage on core business logic.
- Set up CI/CD pipeline (GitHub Actions or similar) for automated testing on every push.
- Fix any data integrity issues and add database-level constraints where missing.
- Add error monitoring (Sentry or similar) and basic application performance monitoring.
- Document the current system architecture and data flow.

### Phase 2: Productise (Weeks 4–7)

**Goal:** Transform from internal tool to multi-tenant SaaS.

- Add **organisation/team model** — separate tenant data, invite team members.
- Add **subscription and billing** (Laravel Cashier + Stripe) with tiered plans.
- Build **onboarding flow** — guided ICP setup, first lead source creation, first research run.
- Add **usage metering** — per-tenant research run counts, AI spend tracking, plan limits enforcement.
- Build a **public marketing landing page** with clear value proposition.
- Add a **second AI provider** (Anthropic Claude) as fallback to reduce single-provider risk.
- Build a minimal **REST API** for future integration possibilities.

### Phase 3: Monetise (Weeks 8–10)

**Goal:** Get paying users.

- Launch **closed beta** to 10–20 agency founders (personal network, communities).
- Offer a **free trial** (14 days or 25 researched accounts) to reduce friction.
- Set pricing at three tiers: Starter (£49/mo, 100 accounts), Growth (£99/mo, 300 accounts), Agency (£249/mo, 1000 accounts + multi-user).
- Implement **basic analytics**: track user activation, research completion rates, outreach generation rates.
- Collect qualitative feedback on research quality, signal accuracy, and outreach usefulness.

### Phase 4: Validate PMF (Weeks 11–13)

**Goal:** Determine whether this has product-market fit.

- Measure **retention**: Are beta users coming back weekly? Are they running research consistently?
- Measure **activation**: What percentage of sign-ups complete their first research run?
- Measure **value delivery**: Are users actually sending the generated outreach? Are they getting responses?
- Identify the **"aha moment"**: At what point do users recognise the value? (Likely: seeing their first completed brief with real signals.)
- Decide: **double down** (invest in growth) or **pivot** (change positioning, target market, or core feature emphasis).

---

## 10. One-Paragraph Investor Pitch

Every B2B service business needs a steady pipeline of qualified prospects, but the research process is brutal — hours of Googling, website browsing, note-taking, and writing personalised outreach for every single lead. Hare & Tortoise Prospect Portal automates this entire workflow using AI. It discovers potential clients via intelligent search, crawls their websites, detects specific signals and opportunities, scores them against your ideal customer profile, generates detailed research briefs, and writes personalised LinkedIn and email outreach — all in minutes, at a fraction of the cost of a human researcher. The platform is already built and functional on a modern Laravel stack with OpenAI integration, prompt versioning, and cost controls. With multi-tenancy and billing added, this becomes a vertical SaaS product serving the thousands of agencies, consultancies, and B2B service businesses that are currently doing this work manually, inconsistently, and expensively.

---

*Document generated from codebase analysis. All statements reflect what exists in the repository as of February 2026. Assumptions are labelled where made.*
