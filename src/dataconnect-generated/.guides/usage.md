# Basic Usage

Always prioritize using a supported framework over using the generated SDK
directly. Supported frameworks simplify the developer experience and help ensure
best practices are followed.





## Advanced Usage
If a user is not using a supported framework, they can use the generated SDK directly.

Here's an example of how to use it with the first 5 operations:

```js
import { createUser, createCourt, createTimeSlot, createBooking, getCurrentUser, listCourts, getCourt, listTimeSlots, myBookings } from '@firebasegen/example-connector';


// Operation CreateUser:  For variables, look at type CreateUserVars in ../index.d.ts
const { data } = await CreateUser(dataConnect, createUserVars);

// Operation CreateCourt:  For variables, look at type CreateCourtVars in ../index.d.ts
const { data } = await CreateCourt(dataConnect, createCourtVars);

// Operation CreateTimeSlot:  For variables, look at type CreateTimeSlotVars in ../index.d.ts
const { data } = await CreateTimeSlot(dataConnect, createTimeSlotVars);

// Operation CreateBooking:  For variables, look at type CreateBookingVars in ../index.d.ts
const { data } = await CreateBooking(dataConnect, createBookingVars);

// Operation GetCurrentUser: 
const { data } = await GetCurrentUser(dataConnect);

// Operation ListCourts: 
const { data } = await ListCourts(dataConnect);

// Operation GetCourt:  For variables, look at type GetCourtVars in ../index.d.ts
const { data } = await GetCourt(dataConnect, getCourtVars);

// Operation ListTimeSlots: 
const { data } = await ListTimeSlots(dataConnect);

// Operation MyBookings: 
const { data } = await MyBookings(dataConnect);


```