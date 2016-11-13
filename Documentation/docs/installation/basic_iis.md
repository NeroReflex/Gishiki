# Basic IIS
You shouldn't be using IIS, really, just... *don't*.

If __REALLY HAVE TO__ use IIS and you have no other choice make sure you have a
file called Web.config alongside with an index.php file in the same
public-accessible directory.

The Web.config file should contain this code:

```
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="slim" patternSyntax="Wildcard">
                    <match url="*" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>
```

However, you __REALLY__ should keep distances from IIS (at least when using it with PHP)!