### Refactoring in BookingController & BaseRepository
- Removed unused variable / function call
- Added Response in all cases
- Added Default variable
- Simplified if/else to ternary where possible
- Replaced ==/!= with ===/!==
- Renamed variables in snake case  

### Refactoring in BookingRepository
Booking Repository code is too much repeated and the code is quite lengthy.
For that I tried to extract common code out in separate function at 2, 3
places. But still there is a lot to do. It would be nice to break the 
Repository code in other files like service, trait or make jobs 
accordingly.

Also, It uses Mailer, so it should be created as a Job, which should 
be triggered via Listener.  

If I know the context, maybe I am able to achieve more refactoring in this.

Other than these, I did basic refactoring as I did in BookingController.


