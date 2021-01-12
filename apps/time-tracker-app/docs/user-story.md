# User Story

`Provider ID 1`, logs in

- A `page` timer starts counting from `0:00` on the node server

Provider opens a `Patient Overview` page related to `Patient ID 2`

- Node server gets the CCM time for Patient ID 2 (say `00:15:00`), and a running `ccm` timer is started at `00:15:00` and also rendered on the client. Let's call this timer `1-2-ccm`

- Node server creates an activity:
    ```php
        [
            'start_time' => '2017-12-01 12:00:00',
            'end_time' => '',
            'duration'   => '',
            'url'    => 'patient overview url',
            'url_short'   => 'patient overview url_short',
            'name'   => 'Patient Overview',
            'title'      => 'patient.overview.route',
        ]
    ```

30 seconds later Provider opens a new tab for `View Careplan` page related to `Patient ID 2`

- `1-2-ccm` running timer is rendedred on the client, starting at `00:15:30`

- `page` timer reads `0:30`

- Node server modifies existing activity:
    ```php
        [
            'start_time' => '2017-12-01 12:00:00',
            'end_time' => '2017-12-01 12:01:00',
            'duration'   => '60',
            'url'    => 'patient overview url',
            'url_short'   => 'patient overview url_short',
            'name'   => 'Patient Overview',
            'title'      => 'patient.overview.route',
        ]
    ```

- Node Server submits above activity to CPM-API.

- Node server creates an activity:
    ```php
        [
            'start_time' => '2017-12-01 12:01:00',
            'end_time' => '',
            'duration'   => '',
            'url'    => 'View Careplan url',
            'url_short'   => 'View Careplan url_short',
            'name'   => 'View Careplan',
            'title'      => view.careplan.route',
        ]
    ```

 15 seconds later Provider returns to previous `Patient Overview` tab related to `Patient ID 2`

- `1-2-ccm` running timer is rendedred on the client, starting at `00:15:45`.

- `page` timer reads `0:45`

- Node server modifies existing activity:
    ```php
        [
            'start_time' => '2017-12-01 12:01:00',
            'end_time' => '2017-12-01 12:01:15',
            'duration'   => '15',
            'url'    => 'View Careplan url',
            'url_short'   => 'View Careplan url_short',
            'name'   => 'View Careplan',
            'title'      => view.careplan.route',
        ]
    ```

- Node Server submits above activity to CPM-API.

- Node server creates an activity:
    ```php
        [
            'start_time' => '2017-12-01 12:01:15',
            'end_time' => '',
            'duration'   => '',
            'url'    => 'patient overview url',
            'url_short'   => 'patient overview url_short',
            'name'   => 'Patient Overview',
            'title'      => 'patient.overview.route',
        ]
    ```
