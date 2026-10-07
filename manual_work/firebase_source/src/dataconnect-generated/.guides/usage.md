# Basic Usage

Always prioritize using a supported framework over using the generated SDK
directly. Supported frameworks simplify the developer experience and help ensure
best practices are followed.





## Advanced Usage
If a user is not using a supported framework, they can use the generated SDK directly.

Here's an example of how to use it with the first 5 operations:

```js
import { upsertUser, updateUserProfile, updateUser, deleteUser, createCourt, updateCourtStatus, updateCourt, deleteCourt, createTimeSlot, createBooking } from '@firebasegen/example-connector';


// Operation UpsertUser:  For variables, look at type UpsertUserVars in ../index.d.ts
const { data } = await UpsertUser(dataConnect, upsertUserVars);

// Operation UpdateUserProfile:  For variables, look at type UpdateUserProfileVars in ../index.d.ts
const { data } = await UpdateUserProfile(dataConnect, updateUserProfileVars);

// Operation UpdateUser:  For variables, look at type UpdateUserVars in ../index.d.ts
const { data } = await UpdateUser(dataConnect, updateUserVars);

// Operation DeleteUser:  For variables, look at type DeleteUserVars in ../index.d.ts
const { data } = await DeleteUser(dataConnect, deleteUserVars);

// Operation CreateCourt:  For variables, look at type CreateCourtVars in ../index.d.ts
const { data } = await CreateCourt(dataConnect, createCourtVars);

// Operation UpdateCourtStatus:  For variables, look at type UpdateCourtStatusVars in ../index.d.ts
const { data } = await UpdateCourtStatus(dataConnect, updateCourtStatusVars);

// Operation UpdateCourt:  For variables, look at type UpdateCourtVars in ../index.d.ts
const { data } = await UpdateCourt(dataConnect, updateCourtVars);

// Operation DeleteCourt:  For variables, look at type DeleteCourtVars in ../index.d.ts
const { data } = await DeleteCourt(dataConnect, deleteCourtVars);

// Operation CreateTimeSlot:  For variables, look at type CreateTimeSlotVars in ../index.d.ts
const { data } = await CreateTimeSlot(dataConnect, createTimeSlotVars);

// Operation CreateBooking:  For variables, look at type CreateBookingVars in ../index.d.ts
const { data } = await CreateBooking(dataConnect, createBookingVars);


```