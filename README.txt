Advance Mindmap module for Moodle 2.x
-------------------------------------

==Author==
Andy Chan
Contributed by the project "Learning 2.0
An Online Platform and a Teacher Support Network for Curriculum and Assessment Innovation in Liberal Studies for the NSS Curriculum"
(2008-2012, http://learn20.cite.hku.hk)

==Description==
This is a modification of the original Mindmap module for moodle formerly found on ekpenso.com. 
This Advance Mindmap module allows you to create and save simple mindmaps from within moodle.

Added features include:
- Every user now have a separate mindmap in one single activity, with links to view others' mindmap.
- Dummy group mode: multiple mindmaps with simple group name that everyone can view and edit (for group mindmapping without having to setup groups in the course) 
- Lock mindmap: prevent editing of mindmap when someone is editing it during group mindmapping

Currently, it is not backward compatible with Moodle 1.9x, please install it on Moodle 2.x only.

==Installation==
- Copy the "/advmindmap" folder and place it into the /mod directory
- Login as administrator
- Go to the "Notifications" page under "Site administration"
- Moodle should detect a new module automatically, follow the onscreen instruction to install it

==FAQ==
Q1: How do I add a child node in 2nd, 3rd, 4th...nth level?
A1: First, select the node you want to add child node to, then click the "+" icon. You can also press the "Insert" button on your keyboard.

Q2: How to prevent students from viewing others' mindmap?
A2: Override the permission of the activity: remove "View Other" from student role.

Q3: I did not saw the mind map editor/ I only saw a blank page, how do I created a mind map?
A3: Please make sure you have installed Adobe Flash Player.

==Links==
You can also take a look at another fork of the mindmap module for Moodle 2.X by t6enis here
https://github.com/t6nis/moodle-mod_mindmap

Thanks to original mindmap module author: Andreas Geier
Link: https://github.com/functino/Moodle-Mindmap-Module

Plugin icon is from the "Onebit" icon set downloaded from Icojam
Link: http://www.icojam.com/blog/?p=177#more-177