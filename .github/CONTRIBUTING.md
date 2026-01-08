# Contributing Guidelines

> [!IMPORTANT]
> This documentation concerns the development of the VHeart platform. If you have any content/moderation concerns, **please contact staff on the [Discord server](https://discord.com/invite/ThVZHqvXnD) instead!**

Thank you for considering to contribute to the development of VHeart, here's some information in the hows-and-whys of it.

## Reporting Bugs

If you want to report bugs, please do the following:

* Before creating an issue in this repository, please make sure the issue isn't already reported. Check [the list of issues](https://github.com/VHeart-Clips/VHeart_Webseite/issues) for that!
  * If the issue is already reported, feel free to add any additional details the original report might have missed. This helps us solving issues faster!
* [Create your issue!](https://github.com/VHeart-Clips/VHeart_Webseite/issues/new)
  * Try to be as descriptive as possible and include as much information as you possibly can to help us solve the issue, for example:
    * A link to the page where the issue occured (or on what page it happened, e.g. _Clip Submission_)
    * A screenshot of the issue, if it is of visual nature.
    * What browser you are using (browser names by itself are good already, with version numbers is even better!)

## Submitting Pull Requests

You want to help with code contributions? That's awesome!

If no issue exists for the bug you want to fix or feature you want to implement, please [create one](https://github.com/VHeart-Clips/VHeart_Webseite/issues/new) ahead of time.

You need to create issues for feature requests first, because this allows us to discuss your idea with us. VHeart has a very clear vision of how the website should work, so not every feature idea might be an optimal fit. **However**, your idea might be actually useful! This allows us to understand your point, and also potentially prevents you from wasting a lot of time in case we don't agree with your feature idea!

Once that is done, do the following:

* [Fork this repository.](https://github.com/VHeart-Clips/VHeart_Webseite/fork)
* [Setup a local development environment.](/docs/development/setup.md)
* Create a branch for your topic.
  * This allows you to keep the `main` branch up to date with upstream over time, if you plan to contribute multiple things.
  * The naming format for branches is `username/my-topic` where `username` is your GitHub username and `my-topic` is a 1-3 word summary of your change (e.g. `pixeldesu/clip-submit-error`)
* Work on your changes!
* Push the branch to your fork.
* [Create a Pull Request](https://github.com/VHeart-Clips/VHeart_Webseite/compare) with your created branch.
* Make sure that all GitHub workflows pass, this includes testing and linting stages.