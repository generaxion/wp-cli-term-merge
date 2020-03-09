# WP-CLI Term Merge

**Contributors:** [Teemu Suoranta](https://github.com/TeemuSuoranta)

**Tags:** WordPress, WP-CLI, Terms

**License:** GPLv2 or later

## Description

Merge two terms into one. Move all posts from term A to term B and optionally delete term A after that.

### How to use

Basic (all post types, image size large):

`wp term-merge run --from={term_id 1} --to={term_id 2}`

`wp term-merge run --from=123 --to=321`

Skip deleting the term:

`wp term-merge run --from={term_id 1} --to={term_id 2} --skip-delete`

### Output

```
$ wp term-merge run --from=123 --to=321
Success: Done: 10 posts moved from #123 (Books) to #321 (Literary)
Success: Deleted term #123 (Books)
```

## Disclaimer

You should backup your database before running this just in case.
