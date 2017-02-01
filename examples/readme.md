This sample CLI script exports my Withings data.

## Configuration

Create a `.apikeys` file with the following structure:

```json
{
  "api_key": "12345d344fc5efe59c561c35b9cd11e65e8f911b5eb99312345",
  "api_secret": "1234542f328db37d82dbf55f8567868451336c4edb76c75612345"
}
```

## Execution

```shell
# Export sleep, body or activity data, starting from the specified date
$ php cli.php --sleep --since=2015-12-01
$ php cli.php --body --since=2015-12-01
$ php cli.php --activity --since=2014-03-01
```

Exported files are stored in the `data` directory.

The resulting data looks like this:

`data/sleep.json`:

```json
[
    {
        "start": "2016-01-14 08:18",
        "end": "2016-01-14 08:26",
        "state": "awake"
    },
    {
        "start": "2016-01-15 00:56",
        "end": "2016-01-15 00:59",
        "state": "light"
    },
    {
        "start": "2016-01-15 00:59",
        "end": "2016-01-15 01:05",
        "state": "deep"
    },
```

`data/body.json`:

```json
{
    "2015-03-02 23:47": {
        "weight": 72.14,
        "fat_free_mass": 61.277,
        "fat_ratio": 15.059,
        "fat_mass_weight": 10.863
    },
    "2015-03-03 22:33": {
        "weight": 71.78,
        "fat_free_mass": 61.887,
        "fat_ratio": 13.782,
        "fat_mass_weight": 9.893
    },
```

`data/activity.json`:

```json
{
   "2016-02-02": {
        "steps": 1834,
        "distance": 1578.67,
        "calories": 57.712
    },
    "2016-02-03": {
        "steps": 5387,
        "distance": 4720.34,
        "calories": 173.463
    },
    "2016-02-04": {
        "steps": 1428,
        "distance": 1383.14,
        "calories": 50.844
    },
```
