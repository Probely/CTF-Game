# Pixels Camp Security CTF Dashboard

This is the repo for the Pixels Camp Security CTF Dashboard, which includes

* a public dashboard
* a private team area to submit answers/follow progress
* a backoffice for the organization

Public dashboard:
![Public dashboard]
(https://github.com/Probely/CTF-Game/screenshots/public dashboard.png)

Private team area:
![Private team area]
(https://github.com/Probely/CTF-Game/screenshots/team dashboard.png)

Organization backoffice:
![Organization backoffice]
(https://github.com/Probely/CTF-Game/screenshots/backoffice.png)

Main features of the dashboard

* start/stop the CTF
* pause the counter (adds minutes)
* anouncements system to teams
* logs of correct answer submissions (in the backoffice)
* logs of submissions (through syslog)


## Setup

Each team gets a token to login in their private area. Set them up at the ```teams``` table, [here](https://github.com/Probely/CTF-Game/blob/master/sql/schema.sql). Don't reuse the tokens or else teams from previous CTFs could login as an opponent team.

Run ```docker-compose up```. This makes the dashboard available at localhost:8050

The private team area is at [localhost:8050](http://localhost:8050). You can login with one of the tokens from the ```teams``` table.

The backoffice will be running [here](http://localhost:8050/boctf.php). You should (really!) protect this URL with some basic auth, or some other kind of ACL.