# VHeart Website

## Sail Setup (fürs erste)

> Please note that you have to run this in a linux environment. If you are on Windows you can use the [Windows Subsystem for Linux](https://docs.microsoft.com/en-us/windows/wsl/install-win10) to run this project.

To initially setup the project run `composer install` to be able to use sail.

You can run this commands in case you have no php or composer installed on your system (requires docker):

```bash
# Copy .env.example to .env and change the values to your needs
cp .env.example .env

# Install required dependencies to make sail work (you can ignore errors)
docker run --rm --user $(id -u):$(id -g) -v $(pwd):/app composer install --ignore-platform-reqs

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

# Done! You can now access the app at http://localhost

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


## Resources

- [Tips & Tricks for windows and WSL](https://jessehouwing.net/tips-tricks-git-under-wsl-and-windows/)
- [InertiaJS Documentation](https://inertiajs.com/docs/v2/getting-started)
- [Laravel Documentation](https://laravel.com/docs/12.x/configuration)
- [Twitch API Documentation](https://dev.twitch.tv/docs/api/reference)
- [React Documentation](https://react.dev/reference/react)
  - [shadcn UI Stuff](https://ui.shadcn.com/)
- [FrankenPHP (if we use it)](https://frankenphp.dev/)
