# Application Domain Logic

## Domain

- This is a personal workout-tracking app specifically for weightlifting.
- A user can have many workout programs.
- A program is an overall workout plan, such as StrongLifts 5x5.
- A program consists of one or more workouts.
- A program can contain one to seven repeating workouts: `A`, `A/B`, `A/B/C`, `A/B/C/D`, `A/B/C/D/E`, `A/B/C/D/E/F`, or `A/B/C/D/E/F/G`.
- `A` means the same workout is used for every workout entry. `A/B` means the program has two workouts, and so on.
- A workout defines the exercises that belong to one part of a program. For example, StrongLifts 5x5 has:
  - Workout A: squats, barbell rows, and bench press
  - Workout B: squats, deadlift, and overhead press
- A workout entry records a workout that the user actually performed, including the reps and weights completed.

## Program Sequence

- The letter sequence is a reminder of the intended order, not a schedule.
- Programs do not dictate which days users work out.
- Users can record any workout at any time and do not have to follow the sequence.
- The workouts page provides a separate tab for each workout so users can record them in any order.

## Workout Entries

- When a user works out, they create a new workout entry for the selected workout.
- A new workout entry defaults to the current date in the user's configured timezone.
- Users may add exercises to the current workout entry.
- Users may remove any exercise from the current workout entry.
- Adding or removing exercises from an entry does not change the program, workout, or other workout entries.
- Exercise lists are therefore not guaranteed to remain constant across entries for the same workout.
- Exercise picker should have type filtering (combobox behavior)

## Exercise Copying

- A new workout entry copies exercises from the most recent previous entry for the same workout.
- Copying an exercise includes:
  - the exercise name
  - its order in the workout
  - its previous weight
  - its set count
  - its previous reps
- Notes are not copied from the previous workout entry.
- If the workout has no previous entry, the new workout entry starts with zero exercises and zero sets. The user adds exercises during the first workout.
- Exercises are reusable records represented as backend enums.
- The app supports both kilograms (`kg`) and pounds (`lbs`).
- Weight units are selected independently for each exercise.

## Workout Entry Lifecycle

- Workout entries do not need a draft state.
- Users can edit a workout entry after it has been completed.
- Users can delete workout entries.
- Users can backdate a workout entry.
- Users cannot future-date a workout entry.

## Validation

- Recorded weights must be greater than zero.
- Recorded reps must be greater than zero.
- An empty workout entry is valid.
- Duplicate exercises in a workout entry are valid. A user may return to an exercise later in the workout, for example to perform volume sets after doing another exercise.

## Historical Data

- Changes to a program or workout do not affect historical workout entries.
- If a program or exercise enum is no longer available, historical data must not be altered.
- Historical entries preserve the exercises and values that were recorded at that time.

## Users, Ownership, and Settings

- Users can only view and modify their own programs, workouts, and workout entries.
- The user's timezone is a user setting and is used when assigning workout dates.
  
