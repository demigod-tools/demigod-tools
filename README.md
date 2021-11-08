
# Demigod Tools

[![CircleCI](https://circleci.com/gh/pantheon-systems/terminus-plugin-example.svg?style=shield)](https://circleci.com/gh/pantheon-systems/terminus-plugin-example)
[![Terminus v3.x Compatible](https://img.shields.io/badge/terminus-03.x-green.svg)](https://github.com/pantheon-systems/terminus-plugin-example/tree/3.x)

A simple plugin for Terminus-CLI version 3.0 or newer that adds a docker compose and environment variables.

Adds commands 'hello' and 'auth:hello' to Terminus. Learn more about Terminus Plugins in the
[Terminus Plugins documentation](https://pantheon.io/docs/terminus/plugins)

## Requirements

* Terminus version 3.0

  `brew install pantheon-systems/external/terminus`

* (direnv)[https://direnv.net]

  `brew install direnv`

* Docker version 4.0+ ( docker-compose is now a part of default install )

  `brew install --cask docker`

* [Robo](https://github.com/consolidation/robo)

  See the Readme on the robo repository


## Installation

To install this plugin using Terminus 3:

```
terminus self:plugin:install demigod-tools/demigod-tools
```

## Configuration

These commands require no configuration

## Usage

```

terminus local:clone {site_name}

terminus demigod:copy-templates {site_name}

robo docker:up

```

## Update

`terminus self:plugin:update`

