# events-import-export

This is a wordpress plugin that imports events from a local json file, show events in a page and export events in json format.

## How to use

1. Download the plugin zip file, unzip it and place it inside wordpress plugins folder. Alternatively clone the repository inside plugins folder.
2. Activate the plugin
3. As soon as the plugin is activated, required custom post types and pages will be created.
4. An admin page called "Manage Events" will be created in wp-admin
5. Options to import events, show events and export events will be found in "Manage Events" admin page.
6. Install "Advanced Custom Fields" plugin to see properly all the custom fields in event post edit page.
7. After install "Advanced Custom Fields" plugin, go to "Custom Fields > Field Groups", sync should be available there. Please make a sync to get the field group.
8. Import can be done either from wp-admin > Manage Events > Import Events button (perform action using ajax) or using wp-cli command

## wp-cli command (import events)
wp import-events

## Important details

1. Events will be imported as custom post "event".
2. Admin menu "Events" is used for this custom posts display.
3. "title" attribute is used as post title and "about" attribute is used as post content. Other attributes are used as custom field, however wp tags taxonomy is used for tags attribute. 
4. Past events will be imported as draft posts and upcoming events will be imported as published posts
5. In frontend, events lists are displayed in this url - SiteURL/loop-events-list/ (if pretty permalink is used)
6. In this list, events are sorted by timestamps (closest events at the top)
7. Only published events are considered as active events and display in the list
8. In frontend, events can be export in this url - SiteURL/events-export/ (if pretty permalink is used)
9. In the export, events are sorted by timestamps (closest events at the top)
10. Only published/active events are included in export data

## Video - How to use

[![How to use](https://i.ytimg.com/vi/mvD-GTe72eo/maxresdefault.jpg)](https://www.youtube.com/watch?v=mvD-GTe72eo)

## Screenshots

1. Admin page -  Manage Events

![Manage Events page look](http://i.imgur.com/oaGqMAu.png)

2. Events Custom post type

![event cpt](http://i.imgur.com/0ggfXAL.png)
![events list in wp-admin](http://i.imgur.com/XozrL8b.png)

3. Field group sync in Advanced custom field

![field group sync](https://i.imgur.com/ebwEhZc.jpg)

4. Custom fields display

![Custom fields display](http://i.imgur.com/eQyNQpd.png)

5. Events list and export page in wp-admin

![events list and export page](http://i.imgur.com/TumFujL.png)

6. Events lists in frontend

![events lists](https://i.imgur.com/Cu8LY0f.jpg)

7. Events export

![events export](http://i.imgur.com/UkHWcuk.png)