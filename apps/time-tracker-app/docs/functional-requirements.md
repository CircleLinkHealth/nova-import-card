# Functional Requirements

## Off Page Count

- [x] The time spent off a CCM page should still be counted
  - [x] If the time spent off a CCM page is > 2 mins, then show a modal with (yes/no) options
    - [x] If the user clicks yes, add the full time spent off the page
    - [x] If the user clicks no, add only 30 seconds
  - [x] If the time spent off a CCM page is > 10 mins, then add only 30 seconds and sign the user off

## Page Load Count

- [x] The time it takes for a CCM page to load should add to the displayed time

## Activity Times

- [x] The times should be recorded separately for different page activities in a session

## Tab Switch

- [x] When switching between CCM page tabs, the time sync should be seamless (no time jumps)

## Non CCM Page Track

- [x] Pages that do not belong to patients should still be tracked in the background, and the time recorded on these pages, shouldn't affect the times recorded on CCM pages e.g. the [Patient Activity Report](https://staging.careplanmanager.com/manage-patients/334/activities) page
