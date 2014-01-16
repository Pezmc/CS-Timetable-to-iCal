CS-Timetable-to-iCal
====================

Convert the CS Manchester Timetable pages into iCal files.

This app scrapes the CS Manchester website and creates your timetable from it. It then converts this timetable to an ics file for import into your favourite calendar application.

Getting Involved
====

*Note: Code is has been getting more and more hacky as I'ved rushed to finish. Please excuse!*

Please just send pull requests with any enchancements or bug fixes.

How To Use
===
You can try the [live demo](http://labs.pezcuckow.com/cstimetable/) or do the following to run it on your local machine.

1. Download the repo with `git clone`
2. Use `git submodule init && git submodule update` to pull in Twig
3. Allow writing to the cache folder with `chmod -R 777 cache`
4. Serve the PHP with a server
5. Open index.html and follow the steps to generate your timetable


Todo
====

- ~~Handle two hour events~~ Done
- ~~Add a nicer frontend~~ Done
- See [the enhancements listed in the issues](https://github.com/Pezmc/CS-Timetable-to-iCal/issues?labels=enhancement)

Authors
====
	Pez Cuckow	(email <at> pezcuckow.com)

License
====
    This project is licensed under the Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.
    To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/3.0/

### TLDR License

**Can**
- This software can be modified.
- The software may be distributed.

**Cannot**
- This software cannot be used for commercial purpose.
- You must retain the original copyright and license
- You cannot place a warranty on the software
- Software is released without warranty and the software/license owner cannot be charged for damages.

**Must**
- You must retain the original copyright.
