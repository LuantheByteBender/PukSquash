# Basic Usage

Always prioritize using a supported framework over using the generated SDK
directly. Supported frameworks simplify the developer experience and help ensure
best practices are followed.





## Advanced Usage
If a user is not using a supported framework, they can use the generated SDK directly.

Here's an example of how to use it with the first 5 operations:

```js
import { upsertUser, updateUserProfile, createCourt, updateCourtStatus, createTimeSlot, createBooking, cancelBooking, addBookingPlayer, leaveBooking, createCoachAvailability } from '@firebasegen/example-connector';


// Operation UpsertUser:  For variables, look at type UpsertUserVars in ../index.d.ts
const { data } = await UpsertUser(dataConnect, upsertUserVars);

// Operation UpdateUserProfile:  For variables, look at type UpdateUserProfileVars in ../index.d.ts
const { data } = await UpdateUserProfile(dataConnect, updateUserProfileVars);

// Operation CreateCourt:  For variables, look at type CreateCourtVars in ../index.d.ts
const { data } = await CreateCourt(dataConnect, createCourtVars);

// Operation UpdateCourtStatus:  For variables, look at type UpdateCourtStatusVars in ../index.d.ts
const { data } = await UpdateCourtStatus(dataConnect, updateCourtStatusVars);

// Operation CreateTimeSlot:  For variables, look at type CreateTimeSlotVars in ../index.d.ts
const { data } = await CreateTimeSlot(dataConnect, createTimeSlotVars);

// Operation CreateBooking:  For variables, look at type CreateBookingVars in ../index.d.ts
const { data } = await CreateBooking(dataConnect, createBookingVars);

// Operation CancelBooking:  For variables, look at type CancelBookingVars in ../index.d.ts
const { data } = await CancelBooking(dataConnect, cancelBookingVars);

// Operation AddBookingPlayer:  For variables, look at type AddBookingPlayerVars in ../index.d.ts
const { data } = await AddBookingPlayer(dataConnect, addBookingPlayerVars);

// Operation LeaveBooking:  For variables, look at type LeaveBookingVars in ../index.d.ts
const { data } = await LeaveBooking(dataConnect, leaveBookingVars);

// Operation CreateCoachAvailability:  For variables, look at type CreateCoachAvailabilityVars in ../index.d.ts
const { data } = await CreateCoachAvailability(dataConnect, createCoachAvailabilityVars);


```