# Kanso Framework Tests

Here you'll find all the Kanso framework tests. They are divided in to groups so you can easily run the tests you want.

	php /usr/local/bin/phpunit  --group unit

	php /usr/local/bin/phpunit  --exclude-group integration

| Group                | Description                                                           |
|----------------------|-----------------------------------------------------------------------|
| unit                 | All unit tests                                                        |
| integration          | All integration tests                                                 |
| integration:database | All integration tests that touch the database                         |
| slow                 | All slow tests (both unit and integration)                            |