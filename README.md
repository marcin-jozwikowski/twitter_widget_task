# Twitter Widget Task
Test Task

##Running Docker
* Add `twitter.loc` domain to your local hosts
* Run `docker-compose up -d` to start web server  
* Run `docker exec -ti -u application twt-web bash -c "composer install"` to install PHP dependencies
* Run `docker run --rm -ti -v "$PWD":/app -w /app node yarn install` to install JS dependencies
* Run `docker run --rm -ti -v "$PWD":/app -w /app node yarn encore prod` to build JS and CSS assets

Timetracking:
* _30.07.2019_ - 1h - Twitter API research and seting up project
* _31.07.2019_ - 2h45m - Routing, API Connection with most frequent errors cought, Retrieving raw response and parsing it to only fields required
* _1.08.2019_ - 4h15m - VueJS Widget using Twitter API results + better example display
* _2.08.2019_ - 3h20m - Webpack Encore + docker build; Bootstrap + Username Form; Improved script init and error handling