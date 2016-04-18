# Configuration
Gishiki is a bit tricky to configure, but don't worry: you just need to copy the
structure of the JSON file below and change what you don't like:

the real configuration file and application descriptor is stored inside the application
directory and is called settings.json

It has a fixed (minumum) structure:
```
{
    "general": {
        "development": true,
        "autolog": null
    },

    "security": {
        "serverPassword": "{{@MASTER_KEY}}",
        "serverKey": "{{@SERVER_KEY}}"
    }
}
```

As you might have thought those {{@VAR_NAMES}} are replaced with constants defined
in your environment AND/OR Heroku "Config Variables"!

This is a GREAT feature that keeps SECRET your database connection descriptor and
your master server key while allowing application portability among illimitate environments!


## Advanced Configuration
Everything about configuration is explained in the proper section:

for example you can find the explanation of of the database connection in the 
database [chapter](database/).