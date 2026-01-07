# Development Setup

## Prerequisites

* A Linux environment. If you are on Windows you can use the [Windows Subsystem for Linux](https://docs.microsoft.com/en-us/windows/wsl/install-win10) to run this project.
* Twitch OAuth tokens for local development.
  * You can generate these in the [Twitch Developer Console](https://dev.twitch.tv/console)
    * The OAuth redirect URL for local development is `http://localhost/auth/twitch/callback`
  * (If not already done) copy the `.env.example` file over to `.env`
  * Set your Twitch application client ID and client secret as the matching environment variables (`TWITCH_CLIENT_ID` / `TWITCH_CLIENT_SECRET`)

## Sail Setup

This project uses Sail as its recommended development setup. You can either set it up locally, already having PHP installed, or using Docker.

### Local

To initially setup the project run `composer install` to be able to use sail.

### Docker

You can run this commands in case you have no php or composer installed on your system:

```bash
# Copy .env.example to .env and change the values to your needs
cp .env.example .env

# Install required dependencies to make sail work (you can ignore errors)
docker run --rm --user $(id -u):$(id -g) -v $(pwd):/app composer install --ignore-platform-reqs
```

### Universal

Once Sail is set up, follow up by running these commands:

```bash
# now we can start sail (this will take a while)
./vendor/bin/sail up -d

# now we can install the dependencies
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# now we can generate the app key and migrate the database
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate

# Seed the database with required data
./vendor/bin/sail artisan db:seed

# Build development assets (this can be left running)
./vendor/bin/sail npm run dev

# Done! You can now access the app at http://localhost
```

## Sail Extras

Here are some further commands to work with Sail:

```bash
# If you want to stop sail run
./vendor/bin/sail down

# to cleanup the volumes run (this will delete all data, but not the cached images)
./vendor/bin/sail down -v

# to cleanup the volumes and the cached images run
./vendor/bin/sail down -v -c

# to rebuild the images run  (this will take a while)
./vendor/bin/sail build --no-cache

# to share the app with others run
./vendor/bin/sail share

# If you want to run browser based tests you also have to setup playwright (will take a while and some space):
./vendor/bin/sail npx playwright install
```

To make working with sail easier you can add the following aliases to your `.bashrc` or `.zshrc`:

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

If you work with WSL on Windows you may have to add this to your ~/.gitconfig inside WSL:

```ini
[credential]
    helper = /mnt/c/Program\\ Files/Git/mingw64/bin/git-credential-manager.exe
```
(make sure you have up-to-date git installed on windows though)

this allows the git inside WSL to use your window's git credentials.