# Achievement System Plan

## Product Direction

Use **achievements** rather than streaks. Strength training does not require daily activity, so the initial system rewards cumulative milestones.

Initial workout-entry achievements:

- 1, 3, 5, and 10 entries
- 25 entries
- Every 25 entries after that, initially through 250

Achievements are permanent once unlocked. Deleting an entry does not revoke an achievement. Existing users will be backfilled silently when the feature launches via an Artisan command run in Laravel Cloud.

## Evaluation

Evaluate achievements eagerly and synchronously after a completed domain action. Do not use a cron job as the primary mechanism.

Do not use Eloquent observers as the authoritative trigger. `WorkoutEntry::created` occurs before exercises and sets are saved, and `Program::created` occurs before its A/B/C/etc. workouts are complete. Use explicit transactional application actions instead:

```text
Livewire action
    -> application action and transaction
    -> save the complete aggregate
    -> evaluate relevant achievement definitions
    -> record new user achievements
    -> commit
    -> redirect or refresh
    -> global modal displays pending achievements
```

Planned actions:

- `app/Actions/CreateProgram.php`
- `app/Actions/CreateWorkoutEntry.php`
- `app/Actions/UpdateWorkoutEntry.php`
- `app/Actions/DeleteWorkoutEntry.php`

Entry updates should become transactional because the current edit flow deletes and recreates exercises and sets.

Use a central evaluator such as `App\Achievements\AchievementEvaluator`. It should evaluate predefined definitions relevant to the triggering action and award every newly eligible definition, not only an exact threshold match.

Future achievement categories may include:

- Cumulative lifted volume
- Number of programs created
- Program shapes such as A, A/B, and A/B/C
- Exercise variety
- Personal records

New categories may require application code. The system does not need a configurable rules engine initially.

## Database Design

Use a fixed catalog of achievement definitions and a user pivot/ledger table.

### `achievements`

- `id`
- `key` unique
- `name`
- `description`
- `category`
- `threshold` nullable
- `is_secret` boolean
- `thumbnail_path` nullable
- `artwork_path` nullable
- timestamps

### `user_achievements`

- `id`
- `user_id`
- `achievement_id`
- `unlocked_at`
- `announced_at`
- timestamps

Add a unique constraint on `(user_id, achievement_id)` and indexes supporting user unlock and unannounced-achievement queries.

`achievement.key` is a stable logical identifier, for example:

```text
workout_entries.count.1
workout_entries.count.25
workout_entries.count.50
```

Definitions should not be deleted or have keys reused after release. Obsolete definitions should eventually be archived instead.

Use the database uniqueness constraint and atomic insertion to make awarding idempotent across retries, multiple tabs, concurrent requests, and backfills.

Add an `achievements()` relationship to `User` and a `userAchievements()` relationship as appropriate.

## Achievement Catalog

Seed the initial catalog through an idempotent seeder with these workout-entry thresholds:

```text
1, 3, 5, 10, 25, 50, 75, 100,
125, 150, 175, 200, 225, 250
```

The catalog is deliberately finite. Adding a future milestone means adding one definition and its tests.

## Assets

Each achievement has two independent assets:

- `thumbnail_path`: small image for the Achievements grid
- `artwork_path`: larger, detailed image for the unlock modal

Initially, commit assets with the application under:

```text
public/images/achievements/
```

Store relative paths in the database, for example:

```text
workout-count/025-thumbnail.webp
workout-count/025-artwork.webp
```

Until final artwork is designed, both locations use a numeric badge placeholder.

### Later Laravel Cloud Migration

When an admin portal is introduced:

1. Create a public Laravel Cloud Object Storage bucket.
2. Install the S3-compatible Flysystem adapter.
3. Copy the existing achievement files using the same relative paths.
4. Change the asset resolver to use `Storage::disk(...)->url(...)`.
5. Configure admin uploads to write directly to object storage.
6. Keep database path values unchanged.
7. Remove bundled files only after verifying the migration.

Do not rely on the local filesystem for admin-uploaded files in Laravel Cloud. Cloud compute storage should be treated as ephemeral and not shared across replicas.

## Achievements Page

Add an authenticated full-page Livewire route:

```text
/achievements
Route name: achievements.index
resources/views/pages/achievements/⚡index.blade.php
```

Add a sidebar navigation item beside Dashboard and Programs.

Display the complete achievement catalog in a responsive grid, ordered by:

1. `category`
2. `threshold`
3. `id` as a deterministic fallback

Unlocked cards show the thumbnail, name, description, and unlock date.

Locked non-secret cards show a generic locked placeholder, the name, and the goal. They never show artwork.

Locked secret cards show only a generic locked placeholder and mystery text. They do not render the real name, requirement, asset paths, or artwork in the HTML.

Public assets provide presentation-level secrecy only. If true access control is needed later, use an authenticated asset route or private object storage with temporary URLs.

## Unlock Modal

Create a shared Livewire component, for example:

```text
resources/views/components/achievements/⚡unlocked-modal.blade.php
```

Mount it in the authenticated sidebar layout after the page slot and outside the existing persisted toast block.

The modal should:

- Query the authenticated user's unannounced achievements.
- Show the larger artwork asset, or a numeric placeholder.
- Display achievement name and description.
- Present multiple pending achievements sequentially.
- Mark each achievement announced when dismissed or continued.
- Link to the Achievements page.

Do not persist the modal component across navigation. Database-backed pending state ensures that an unlock survives refreshes, browser closure, and `wire:navigate` redirects.

Backfilled achievements should be marked announced immediately so they appear on the page without opening historical modal sequences.

## Backfill

Add an idempotent command such as:

```text
php artisan achievements:backfill
```

The command should:

- Process users with `chunkById()`.
- Reuse the normal evaluator.
- Mark backfilled achievements announced.
- Avoid modal/session announcements.
- Be safe to rerun.

Always use this Artisan command for achievement backfills, including the initial production rollout. Do not run a backfill inside a schema migration or automatically during local development; it is unnecessary locally and should be run explicitly in Laravel Cloud when needed.

## Testing

Add Pest coverage for:

- Milestones at 1, 3, 5, 10, 25, 50, and 75.
- No achievement before a threshold.
- Repeated evaluation cannot duplicate a user achievement.
- User ownership isolation.
- Failed transactions do not award achievements.
- Entry edits can cross a future cumulative-stat threshold.
- Deleting entries does not revoke earned achievements.
- Program shape evaluation occurs after all workouts exist.
- Backfill is silent and rerunnable.
- Guests cannot access the Achievements page.
- The page shows both locked and unlocked catalog entries.
- Secret locked achievements hide identifying metadata.
- Pending achievements open the global modal.
- Multiple pending achievements advance sequentially.
- Dismissed achievements do not reopen.
- `wire:navigate`, mobile, desktop, and dark-mode behavior.

Browser tests are appropriate for Flux modal interactions and responsive presentation; Livewire feature tests should cover persistence, authorization, and evaluator behavior.
