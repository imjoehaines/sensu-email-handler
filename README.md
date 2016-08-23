# Sensu Email Handler

## Setup

- `composer install`
- `cp .env.example .env`
- Set up the to/from email and alias values in `.env`
- Set up the handler on your sensu-server

## Usage

Pipe stuff to it like any other Sensu handler:

```sh
cat example.json | php handler.php
```

*NB* currently only handles a single line of input
