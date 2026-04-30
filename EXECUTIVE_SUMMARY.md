# Hare & Tortoise Prospect Portal — Executive Summary

**February 2026**

---

## What It Is

An AI-powered platform that automates B2B prospect research and outreach — from finding potential clients to generating personalised sales messages — replacing hours of manual work with an intelligent, end-to-end pipeline.

---

## The Problem

Every B2B service business needs qualified prospects. Today, that means someone senior spends 30–60 minutes per lead: searching Google, browsing websites, taking notes, assessing fit, and writing personalised outreach. It's slow, inconsistent, expensive, and doesn't scale. Existing tools (CRMs, lead databases, scraping tools, AI writers) each solve a fragment of the problem but none automate the full workflow.

---

## What It Does

The platform runs a five-stage automated pipeline:

| Stage | What Happens | Manual Work Eliminated |
|---|---|---|
| **Discover** | AI-generated search queries find potential clients via Google | Googling for prospects |
| **Research** | Polite web crawler fetches key pages; AI extracts company profiles | Browsing websites, taking notes |
| **Detect** | AI identifies specific signals — outdated content, UX issues, tech debt, growth indicators | Assessing what's wrong / what's relevant |
| **Score** | Prospects scored 0–100 based on ideal customer fit, signal strength, and reachability | Deciding who to prioritise |
| **Generate** | AI produces research briefs and personalised LinkedIn/email outreach | Writing messages from scratch |

**Result:** A prioritised list of scored prospects, each with a detailed brief and ready-to-send personalised outreach — produced in minutes at a cost of pennies per account.

---

## Who It's For

**Primary:** Agency founders and BD leads at B2B service businesses (digital agencies, consultancies, specialist firms) with 1–50 people, who currently prospect alongside delivery work.

**Secondary:** SDR teams and growth teams doing volume outbound who need research depth at scale.

---

## Why It's Different

- **End-to-end pipeline**, not a point solution — discovery through to outreach in one system.
- **Research from live websites**, not stale databases — signals are current and evidence-backed.
- **AI is the engine, not a feature** — six distinct AI applications working in sequence, each with validation and cost tracking.
- **Built-in cost controls** — daily budget caps and research limits prevent runaway spend.

Closest comparison: a verticalised, research-first alternative to Clay or Apollo — but with deeper AI analysis and integrated outreach.

---

## Current State

| Dimension | Status |
|---|---|
| Core pipeline | Fully functional — discovery, research, scoring, briefs, outreach all working |
| Tech stack | Laravel 12, Livewire 3, Tailwind CSS, MySQL, Redis, OpenAI |
| AI integration | GPT-4o and GPT-4o-mini with prompt versioning, output validation, cost tracking |
| Multi-tenancy | Not yet — currently a single-tenant internal tool |
| Billing | Not yet — no subscription or payment system |
| Test coverage | Authentication flows covered; core pipeline lacks automated tests |
| API | No external API — monolithic web application |

---

## Commercial Potential

**Market:** Thousands of B2B agencies and consultancies spend significant time on manual prospecting. There is no dominant tool that automates the full research-to-outreach pipeline.

**Pricing opportunity:** At £0.50–£2.00 per researched account (or £49–£249/month subscription), the ROI is compelling — each account replaces 30–60 minutes of skilled human time.

**Recommended path:** Standalone SaaS with tiered subscriptions, starting with a closed beta of 10–20 agency founders.

---

## Key Risks

- **OpenAI dependency** — single AI provider, no fallback.
- **Test coverage gaps** — core business logic untested, creating reliability and refactoring risk.
- **No multi-tenancy or billing** — significant build required before monetisation.
- **Web scraping fragility** — websites change, block crawlers, or return inconsistent content.
- **Compliance grey areas** — GDPR, web scraping legality, and automated outreach regulations need attention.

---

## 90-Day Path to SaaS

| Phase | Weeks | Focus |
|---|---|---|
| **Stabilise** | 1–3 | Test coverage for core pipeline, CI/CD, error monitoring |
| **Productise** | 4–7 | Multi-tenancy, billing (Stripe), onboarding flow, second AI provider |
| **Monetise** | 8–10 | Closed beta (10–20 users), free trial, three-tier pricing |
| **Validate** | 11–13 | Measure retention, activation, and value delivery; decide scale or pivot |

---

## The Pitch

Every B2B service business burns hours manually researching and reaching out to prospects. This platform automates the entire workflow — it finds potential clients, crawls their websites, detects specific signals and opportunities, scores them against your ideal customer profile, writes detailed research briefs, and generates personalised LinkedIn and email outreach. All in minutes, at pennies per account. The core product is built and functional. With multi-tenancy and billing added, this is a vertical SaaS serving the thousands of agencies and consultancies currently doing this work manually, inconsistently, and expensively.

---

*See [PLATFORM_OVERVIEW.md](PLATFORM_OVERVIEW.md) for the full strategic analysis.*
