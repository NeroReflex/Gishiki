# Configuration
Gishiki is a bit tricky to configure, but don't worry: you just need to remember
3 or 4 things:

the real configuration file and application descriptor is stored inside the application
directory (alongside with the environment.ini file) and is called settings.json

It has a fixed structure:
```
{
    "general": {
        "development": true,
        "autolog": null
    },

    "database_connections": {
            "development":  "{{@DATABASE_URL}}",
            "default": "{{@DATABASE_URL}}",
            "secure_connection": {

            }
    },

    "cache": {
        "enabled": false,
        "server": "memcached://localhost:11211"
    },
    
    "security": {
        "serverPassword": "{{@MASTER_KEY}}",
        "serverKey": "{{@SERVER_KEY}}"
    },
    
    "cookies": {
        "cookiesPrefix": "{{@COOKIES_PRE}}",
        "cookiesEncryptedPrefix": "{{@COOKIES_ENC_PRE}}",
        "cookiesKey": "{{@COOKIES_KEY}}",
        "cookiesExpiration": 5184000,
        "cookiesPath": "/"
    }
}
```

As you might have thought those {{@VAR_NAMES}} are replaced with constants defined
in your environment AND/OR Heroku "Config Variables"!

This is a GREAT feature that keeps SECRET your database connection descriptor and
your master server key while allowing application portability among illimitate environments!
