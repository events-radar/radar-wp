# radar-wp
Radar WP Plugin. Providing a short code to get Radar events into your WP site.

## Quick start

Install the plug-in. Add a shortcode to your content somewhere `[radar_events city=Amsterdam]`.

## Shortcode settings

### So you don't want those fields?

What about something shorter `[radar_events city=Amsterdam fields=title,date,location:address,url]` for example.
Notice the order of the fields is kept; but if you are showing fields from for example the associated groups they will be collected together `[radar_events city=Amsterdam fields=title,date,group:title,location:address,group:link]` the group link and title will be displayed together.

The full set of fields on events presently supported are:
 * title  -  Event title
 * date  -  Event date
 * url  -  Event URL on Radar
 * <del>image</del> /Presently broken in API/
 * price_category:title  -  Price Category
 * price  -  Event price (free text field)
 * email  -  Event e-mail
 * link  -  Event link(s) on the web
 * phone  -  Event phone
 * group:title  -  Event's groups title
 * group:body  -  Groups description
 * group:category:title  -  Groups categories
 * group:topic:title  -  Groups topics
 * group:url  -  Groups URL on Radar
 * <del>group:group_logo</del> /Presently broken in API/
 * group:email  -  Groups public e-mail address
 * group:link  -  Groups link(s) on the web
 * group:phone  -  Groups phone
 * group:opening_times  -  Groups general opening times
 * location:title  -  Event's locations common names
 * location:address  -  Location address
 * location:directions  -  Location additional directions
 * location:location  -  Location geographic location (usually x,y point)
 * category:title  -  Categories
 * topic:title  -  Topics
 * created  -  Date created
 * updated  -  Last updated

### Not that location

Well obviously you can change the *city*. Any city name that you see in the list on the site (be careful to capitalize the same).
But what about a *group*. `[radar_events city=Amsterdam group=41]` for example. You might need help finding the correct number (for now till we put it on the site somewhere easy). Oh! And sorry you do need to put the city in as well, at least for now.

### More events

Just add the `max_count`, the defaut is 5, but `[radar_events city=Amsterdam group=41 fields=title,date,category:title,url max_count=15]` might be good for a block?

### All settings

See the defaults in [function radar_shortcode_events()](https://github.com/events-radar/radar-wp/blob/master/radar.php#L25)

## Different styling

### Something obvious

The present layout and HTML is just what we've come up with so far. If you have better *defaults* let us know, or make a pull request, and we can update this.

### Something different for your site

The values and HTML around them are altered by two filters `radar_shortcode_field_value` and `radar_shortcode_field_html`. You should be able to [`add_filter`](http://codex.wordpress.org/Function_Reference/add_filter) or [`remove_filter`](http://codex.wordpress.org/Function_Reference/remove_filter) to do pretty much anything. Suggestions... let us know.

## To Do (aka as it's a slow, and x language)

 * Needs integrating with WP transients for caching, and can then have cron added to pre-load updates to content.
 * Needs to have a language setting in the [shortcode] - this will make sure the correct translation is pulled (if the event, group etc. is translated), and make sure the links go to the correct language version of the Radar site.
 * Needs to have the strings inside WP translated - but you can do that! They are all in 'radar_textdomain'.
 
