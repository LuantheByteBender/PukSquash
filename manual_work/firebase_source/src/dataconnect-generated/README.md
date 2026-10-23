# Generated TypeScript README
This README will guide you through the process of using the generated JavaScript SDK package for the connector `example`. It will also provide examples on how to use your generated SDK to call your Data Connect queries and mutations.

***NOTE:** This README is generated alongside the generated SDK. If you make changes to this file, they will be overwritten when the SDK is regenerated.*

# Table of Contents
- [**Overview**](#generated-javascript-readme)
- [**Accessing the connector**](#accessing-the-connector)
  - [*Connecting to the local Emulator*](#connecting-to-the-local-emulator)
- [**Queries**](#queries)
  - [*GetUserByFirebaseUid*](#getuserbyfirebaseuid)
  - [*GetBookingsByUserForSlot*](#getbookingsbyuserforslot)
  - [*GetBookingForCell*](#getbookingforcell)
  - [*GetUserById*](#getuserbyid)
  - [*ListActiveCourts*](#listactivecourts)
  - [*GetCourt*](#getcourt)
  - [*ListActiveTimeSlots*](#listactivetimeslots)
  - [*GetAvailabilityData*](#getavailabilitydata)
  - [*GetBookingsByStatus*](#getbookingsbystatus)
  - [*GetBookingsForDate*](#getbookingsfordate)
  - [*GetBookingById*](#getbookingbyid)
  - [*GetBookingsForTimeSlot*](#getbookingsfortimeslot)
  - [*GetTeamPracticeBookingsByCoachDate*](#getteampracticebookingsbycoachdate)
  - [*GetBookingsByUser*](#getbookingsbyuser)
  - [*GetBookingPlayers*](#getbookingplayers)
  - [*GetAvailableCoaches*](#getavailablecoaches)
  - [*IsCoachAvailable*](#iscoachavailable)
  - [*ListAllUsers*](#listallusers)
  - [*ListAllCourts*](#listallcourts)
  - [*GetCoachAvailabilityForDate*](#getcoachavailabilityfordate)
  - [*GetConfirmedBookingForCoachSlot*](#getconfirmedbookingforcoachslot)
  - [*GetConfirmedBookingsForCoachDate*](#getconfirmedbookingsforcoachdate)
  - [*GetConfirmedBookingsByCoach*](#getconfirmedbookingsbycoach)
  - [*GetCoachAvailabilityNoticeBoard*](#getcoachavailabilitynoticeboard)
- [**Mutations**](#mutations)
  - [*UpsertUser*](#upsertuser)
  - [*UpdateUserProfile*](#updateuserprofile)
  - [*UpdateUser*](#updateuser)
  - [*DeleteUser*](#deleteuser)
  - [*CreateCourt*](#createcourt)
  - [*UpdateCourtStatus*](#updatecourtstatus)
  - [*UpdateCourt*](#updatecourt)
  - [*DeleteCourt*](#deletecourt)
  - [*CreateTimeSlot*](#createtimeslot)
  - [*CreateBooking*](#createbooking)
  - [*CancelBooking*](#cancelbooking)
  - [*AddBookingPlayer*](#addbookingplayer)
  - [*LeaveBooking*](#leavebooking)
  - [*CreateCoachAvailability*](#createcoachavailability)
  - [*DeleteCoachAvailabilityForSlot*](#deletecoachavailabilityforslot)

# Accessing the connector
A connector is a collection of Queries and Mutations. One SDK is generated for each connector - this SDK is generated for the connector `example`. You can find more information about connectors in the [Data Connect documentation](https://firebase.google.com/docs/data-connect#how-does).

You can use this generated SDK by importing from the package `@firebasegen/example-connector` as shown below. Both CommonJS and ESM imports are supported.

You can also follow the instructions from the [Data Connect documentation](https://firebase.google.com/docs/data-connect/web-sdk#set-client).

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig } from '@firebasegen/example-connector';

const dataConnect = getDataConnect(connectorConfig);
```

## Connecting to the local Emulator
By default, the connector will connect to the production service.

To connect to the emulator, you can use the following code.
You can also follow the emulator instructions from the [Data Connect documentation](https://firebase.google.com/docs/data-connect/web-sdk#instrument-clients).

```typescript
import { connectDataConnectEmulator, getDataConnect } from 'firebase/data-connect';
import { connectorConfig } from '@firebasegen/example-connector';

const dataConnect = getDataConnect(connectorConfig);
connectDataConnectEmulator(dataConnect, 'localhost', 9399);
```

After it's initialized, you can call your Data Connect [queries](#queries) and [mutations](#mutations) from your generated SDK.

# Queries

There are two ways to execute a Data Connect Query using the generated Web SDK:
- Using a Query Reference function, which returns a `QueryRef`
  - The `QueryRef` can be used as an argument to `executeQuery()`, which will execute the Query and return a `QueryPromise`
- Using an action shortcut function, which returns a `QueryPromise`
  - Calling the action shortcut function will execute the Query and return a `QueryPromise`

The following is true for both the action shortcut function and the `QueryRef` function:
- The `QueryPromise` returned will resolve to the result of the Query once it has finished executing
- If the Query accepts arguments, both the action shortcut function and the `QueryRef` function accept a single argument: an object that contains all the required variables (and the optional variables) for the Query
- Both functions can be called with or without passing in a `DataConnect` instance as an argument. If no `DataConnect` argument is passed in, then the generated SDK will call `getDataConnect(connectorConfig)` behind the scenes for you.

Below are examples of how to use the `example` connector's generated functions to execute each query. You can also follow the examples from the [Data Connect documentation](https://firebase.google.com/docs/data-connect/web-sdk#using-queries).

## GetUserByFirebaseUid
You can execute the `GetUserByFirebaseUid` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getUserByFirebaseUid(vars: GetUserByFirebaseUidVariables, options?: ExecuteQueryOptions): QueryPromise<GetUserByFirebaseUidData, GetUserByFirebaseUidVariables>;

interface GetUserByFirebaseUidRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetUserByFirebaseUidVariables): QueryRef<GetUserByFirebaseUidData, GetUserByFirebaseUidVariables>;
}
export const getUserByFirebaseUidRef: GetUserByFirebaseUidRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getUserByFirebaseUid(dc: DataConnect, vars: GetUserByFirebaseUidVariables, options?: ExecuteQueryOptions): QueryPromise<GetUserByFirebaseUidData, GetUserByFirebaseUidVariables>;

interface GetUserByFirebaseUidRef {
  ...
  (dc: DataConnect, vars: GetUserByFirebaseUidVariables): QueryRef<GetUserByFirebaseUidData, GetUserByFirebaseUidVariables>;
}
export const getUserByFirebaseUidRef: GetUserByFirebaseUidRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getUserByFirebaseUidRef:
```typescript
const name = getUserByFirebaseUidRef.operationName;
console.log(name);
```

### Variables
The `GetUserByFirebaseUid` query requires an argument of type `GetUserByFirebaseUidVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetUserByFirebaseUidVariables {
  firebaseUid: string;
}
```
### Return Type
Recall that executing the `GetUserByFirebaseUid` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetUserByFirebaseUidData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetUserByFirebaseUidData {
  users: ({
    id: UUIDString;
    firebaseUid: string;
    studentNumber?: string | null;
    fullName: string;
    email: string;
    role: string;
    status: string;
    createdAt: TimestampString;
    updatedAt: TimestampString;
  } & User_Key)[];
}
```
### Using `GetUserByFirebaseUid`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getUserByFirebaseUid, GetUserByFirebaseUidVariables } from '@firebasegen/example-connector';

// The `GetUserByFirebaseUid` query requires an argument of type `GetUserByFirebaseUidVariables`:
const getUserByFirebaseUidVars: GetUserByFirebaseUidVariables = {
  firebaseUid: ..., 
};

// Call the `getUserByFirebaseUid()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getUserByFirebaseUid(getUserByFirebaseUidVars);
// Variables can be defined inline as well.
const { data } = await getUserByFirebaseUid({ firebaseUid: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getUserByFirebaseUid(dataConnect, getUserByFirebaseUidVars);

console.log(data.users);

// Or, you can use the `Promise` API.
getUserByFirebaseUid(getUserByFirebaseUidVars).then((response) => {
  const data = response.data;
  console.log(data.users);
});
```

### Using `GetUserByFirebaseUid`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getUserByFirebaseUidRef, GetUserByFirebaseUidVariables } from '@firebasegen/example-connector';

// The `GetUserByFirebaseUid` query requires an argument of type `GetUserByFirebaseUidVariables`:
const getUserByFirebaseUidVars: GetUserByFirebaseUidVariables = {
  firebaseUid: ..., 
};

// Call the `getUserByFirebaseUidRef()` function to get a reference to the query.
const ref = getUserByFirebaseUidRef(getUserByFirebaseUidVars);
// Variables can be defined inline as well.
const ref = getUserByFirebaseUidRef({ firebaseUid: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getUserByFirebaseUidRef(dataConnect, getUserByFirebaseUidVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.users);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.users);
});
```

## GetBookingsByUserForSlot
You can execute the `GetBookingsByUserForSlot` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getBookingsByUserForSlot(vars: GetBookingsByUserForSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByUserForSlotData, GetBookingsByUserForSlotVariables>;

interface GetBookingsByUserForSlotRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsByUserForSlotVariables): QueryRef<GetBookingsByUserForSlotData, GetBookingsByUserForSlotVariables>;
}
export const getBookingsByUserForSlotRef: GetBookingsByUserForSlotRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getBookingsByUserForSlot(dc: DataConnect, vars: GetBookingsByUserForSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByUserForSlotData, GetBookingsByUserForSlotVariables>;

interface GetBookingsByUserForSlotRef {
  ...
  (dc: DataConnect, vars: GetBookingsByUserForSlotVariables): QueryRef<GetBookingsByUserForSlotData, GetBookingsByUserForSlotVariables>;
}
export const getBookingsByUserForSlotRef: GetBookingsByUserForSlotRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getBookingsByUserForSlotRef:
```typescript
const name = getBookingsByUserForSlotRef.operationName;
console.log(name);
```

### Variables
The `GetBookingsByUserForSlot` query requires an argument of type `GetBookingsByUserForSlotVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetBookingsByUserForSlotVariables {
  userId: UUIDString;
  bookingDate: DateString;
  timeSlotId: UUIDString;
}
```
### Return Type
Recall that executing the `GetBookingsByUserForSlot` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetBookingsByUserForSlotData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetBookingsByUserForSlotData {
  bookings: ({
    id: UUIDString;
  } & Booking_Key)[];
}
```
### Using `GetBookingsByUserForSlot`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getBookingsByUserForSlot, GetBookingsByUserForSlotVariables } from '@firebasegen/example-connector';

// The `GetBookingsByUserForSlot` query requires an argument of type `GetBookingsByUserForSlotVariables`:
const getBookingsByUserForSlotVars: GetBookingsByUserForSlotVariables = {
  userId: ..., 
  bookingDate: ..., 
  timeSlotId: ..., 
};

// Call the `getBookingsByUserForSlot()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getBookingsByUserForSlot(getBookingsByUserForSlotVars);
// Variables can be defined inline as well.
const { data } = await getBookingsByUserForSlot({ userId: ..., bookingDate: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getBookingsByUserForSlot(dataConnect, getBookingsByUserForSlotVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getBookingsByUserForSlot(getBookingsByUserForSlotVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetBookingsByUserForSlot`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getBookingsByUserForSlotRef, GetBookingsByUserForSlotVariables } from '@firebasegen/example-connector';

// The `GetBookingsByUserForSlot` query requires an argument of type `GetBookingsByUserForSlotVariables`:
const getBookingsByUserForSlotVars: GetBookingsByUserForSlotVariables = {
  userId: ..., 
  bookingDate: ..., 
  timeSlotId: ..., 
};

// Call the `getBookingsByUserForSlotRef()` function to get a reference to the query.
const ref = getBookingsByUserForSlotRef(getBookingsByUserForSlotVars);
// Variables can be defined inline as well.
const ref = getBookingsByUserForSlotRef({ userId: ..., bookingDate: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getBookingsByUserForSlotRef(dataConnect, getBookingsByUserForSlotVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetBookingForCell
You can execute the `GetBookingForCell` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getBookingForCell(vars: GetBookingForCellVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingForCellData, GetBookingForCellVariables>;

interface GetBookingForCellRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingForCellVariables): QueryRef<GetBookingForCellData, GetBookingForCellVariables>;
}
export const getBookingForCellRef: GetBookingForCellRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getBookingForCell(dc: DataConnect, vars: GetBookingForCellVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingForCellData, GetBookingForCellVariables>;

interface GetBookingForCellRef {
  ...
  (dc: DataConnect, vars: GetBookingForCellVariables): QueryRef<GetBookingForCellData, GetBookingForCellVariables>;
}
export const getBookingForCellRef: GetBookingForCellRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getBookingForCellRef:
```typescript
const name = getBookingForCellRef.operationName;
console.log(name);
```

### Variables
The `GetBookingForCell` query requires an argument of type `GetBookingForCellVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetBookingForCellVariables {
  bookingDate: DateString;
  courtId: UUIDString;
  timeSlotId: UUIDString;
}
```
### Return Type
Recall that executing the `GetBookingForCell` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetBookingForCellData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetBookingForCellData {
  bookings: ({
    id: UUIDString;
  } & Booking_Key)[];
}
```
### Using `GetBookingForCell`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getBookingForCell, GetBookingForCellVariables } from '@firebasegen/example-connector';

// The `GetBookingForCell` query requires an argument of type `GetBookingForCellVariables`:
const getBookingForCellVars: GetBookingForCellVariables = {
  bookingDate: ..., 
  courtId: ..., 
  timeSlotId: ..., 
};

// Call the `getBookingForCell()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getBookingForCell(getBookingForCellVars);
// Variables can be defined inline as well.
const { data } = await getBookingForCell({ bookingDate: ..., courtId: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getBookingForCell(dataConnect, getBookingForCellVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getBookingForCell(getBookingForCellVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetBookingForCell`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getBookingForCellRef, GetBookingForCellVariables } from '@firebasegen/example-connector';

// The `GetBookingForCell` query requires an argument of type `GetBookingForCellVariables`:
const getBookingForCellVars: GetBookingForCellVariables = {
  bookingDate: ..., 
  courtId: ..., 
  timeSlotId: ..., 
};

// Call the `getBookingForCellRef()` function to get a reference to the query.
const ref = getBookingForCellRef(getBookingForCellVars);
// Variables can be defined inline as well.
const ref = getBookingForCellRef({ bookingDate: ..., courtId: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getBookingForCellRef(dataConnect, getBookingForCellVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetUserById
You can execute the `GetUserById` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getUserById(vars: GetUserByIdVariables, options?: ExecuteQueryOptions): QueryPromise<GetUserByIdData, GetUserByIdVariables>;

interface GetUserByIdRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetUserByIdVariables): QueryRef<GetUserByIdData, GetUserByIdVariables>;
}
export const getUserByIdRef: GetUserByIdRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getUserById(dc: DataConnect, vars: GetUserByIdVariables, options?: ExecuteQueryOptions): QueryPromise<GetUserByIdData, GetUserByIdVariables>;

interface GetUserByIdRef {
  ...
  (dc: DataConnect, vars: GetUserByIdVariables): QueryRef<GetUserByIdData, GetUserByIdVariables>;
}
export const getUserByIdRef: GetUserByIdRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getUserByIdRef:
```typescript
const name = getUserByIdRef.operationName;
console.log(name);
```

### Variables
The `GetUserById` query requires an argument of type `GetUserByIdVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetUserByIdVariables {
  id: UUIDString;
}
```
### Return Type
Recall that executing the `GetUserById` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetUserByIdData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetUserByIdData {
  user?: {
    id: UUIDString;
    firebaseUid: string;
    studentNumber?: string | null;
    fullName: string;
    email: string;
    role: string;
    status: string;
    createdAt: TimestampString;
    updatedAt: TimestampString;
  } & User_Key;
}
```
### Using `GetUserById`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getUserById, GetUserByIdVariables } from '@firebasegen/example-connector';

// The `GetUserById` query requires an argument of type `GetUserByIdVariables`:
const getUserByIdVars: GetUserByIdVariables = {
  id: ..., 
};

// Call the `getUserById()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getUserById(getUserByIdVars);
// Variables can be defined inline as well.
const { data } = await getUserById({ id: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getUserById(dataConnect, getUserByIdVars);

console.log(data.user);

// Or, you can use the `Promise` API.
getUserById(getUserByIdVars).then((response) => {
  const data = response.data;
  console.log(data.user);
});
```

### Using `GetUserById`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getUserByIdRef, GetUserByIdVariables } from '@firebasegen/example-connector';

// The `GetUserById` query requires an argument of type `GetUserByIdVariables`:
const getUserByIdVars: GetUserByIdVariables = {
  id: ..., 
};

// Call the `getUserByIdRef()` function to get a reference to the query.
const ref = getUserByIdRef(getUserByIdVars);
// Variables can be defined inline as well.
const ref = getUserByIdRef({ id: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getUserByIdRef(dataConnect, getUserByIdVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.user);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.user);
});
```

## ListActiveCourts
You can execute the `ListActiveCourts` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
listActiveCourts(options?: ExecuteQueryOptions): QueryPromise<ListActiveCourtsData, undefined>;

interface ListActiveCourtsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListActiveCourtsData, undefined>;
}
export const listActiveCourtsRef: ListActiveCourtsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listActiveCourts(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListActiveCourtsData, undefined>;

interface ListActiveCourtsRef {
  ...
  (dc: DataConnect): QueryRef<ListActiveCourtsData, undefined>;
}
export const listActiveCourtsRef: ListActiveCourtsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listActiveCourtsRef:
```typescript
const name = listActiveCourtsRef.operationName;
console.log(name);
```

### Variables
The `ListActiveCourts` query has no variables.
### Return Type
Recall that executing the `ListActiveCourts` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListActiveCourtsData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListActiveCourtsData {
  courts: ({
    id: UUIDString;
    name: string;
    description?: string | null;
    status: string;
    createdAt: TimestampString;
    updatedAt: TimestampString;
  } & Court_Key)[];
}
```
### Using `ListActiveCourts`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listActiveCourts } from '@firebasegen/example-connector';


// Call the `listActiveCourts()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listActiveCourts();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listActiveCourts(dataConnect);

console.log(data.courts);

// Or, you can use the `Promise` API.
listActiveCourts().then((response) => {
  const data = response.data;
  console.log(data.courts);
});
```

### Using `ListActiveCourts`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listActiveCourtsRef } from '@firebasegen/example-connector';


// Call the `listActiveCourtsRef()` function to get a reference to the query.
const ref = listActiveCourtsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listActiveCourtsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.courts);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.courts);
});
```

## GetCourt
You can execute the `GetCourt` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getCourt(vars: GetCourtVariables, options?: ExecuteQueryOptions): QueryPromise<GetCourtData, GetCourtVariables>;

interface GetCourtRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetCourtVariables): QueryRef<GetCourtData, GetCourtVariables>;
}
export const getCourtRef: GetCourtRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getCourt(dc: DataConnect, vars: GetCourtVariables, options?: ExecuteQueryOptions): QueryPromise<GetCourtData, GetCourtVariables>;

interface GetCourtRef {
  ...
  (dc: DataConnect, vars: GetCourtVariables): QueryRef<GetCourtData, GetCourtVariables>;
}
export const getCourtRef: GetCourtRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getCourtRef:
```typescript
const name = getCourtRef.operationName;
console.log(name);
```

### Variables
The `GetCourt` query requires an argument of type `GetCourtVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetCourtVariables {
  id: UUIDString;
}
```
### Return Type
Recall that executing the `GetCourt` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetCourtData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetCourtData {
  court?: {
    id: UUIDString;
    name: string;
    description?: string | null;
    status: string;
    createdAt: TimestampString;
    updatedAt: TimestampString;
  } & Court_Key;
}
```
### Using `GetCourt`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getCourt, GetCourtVariables } from '@firebasegen/example-connector';

// The `GetCourt` query requires an argument of type `GetCourtVariables`:
const getCourtVars: GetCourtVariables = {
  id: ..., 
};

// Call the `getCourt()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getCourt(getCourtVars);
// Variables can be defined inline as well.
const { data } = await getCourt({ id: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getCourt(dataConnect, getCourtVars);

console.log(data.court);

// Or, you can use the `Promise` API.
getCourt(getCourtVars).then((response) => {
  const data = response.data;
  console.log(data.court);
});
```

### Using `GetCourt`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getCourtRef, GetCourtVariables } from '@firebasegen/example-connector';

// The `GetCourt` query requires an argument of type `GetCourtVariables`:
const getCourtVars: GetCourtVariables = {
  id: ..., 
};

// Call the `getCourtRef()` function to get a reference to the query.
const ref = getCourtRef(getCourtVars);
// Variables can be defined inline as well.
const ref = getCourtRef({ id: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getCourtRef(dataConnect, getCourtVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.court);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.court);
});
```

## ListActiveTimeSlots
You can execute the `ListActiveTimeSlots` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
listActiveTimeSlots(options?: ExecuteQueryOptions): QueryPromise<ListActiveTimeSlotsData, undefined>;

interface ListActiveTimeSlotsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListActiveTimeSlotsData, undefined>;
}
export const listActiveTimeSlotsRef: ListActiveTimeSlotsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listActiveTimeSlots(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListActiveTimeSlotsData, undefined>;

interface ListActiveTimeSlotsRef {
  ...
  (dc: DataConnect): QueryRef<ListActiveTimeSlotsData, undefined>;
}
export const listActiveTimeSlotsRef: ListActiveTimeSlotsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listActiveTimeSlotsRef:
```typescript
const name = listActiveTimeSlotsRef.operationName;
console.log(name);
```

### Variables
The `ListActiveTimeSlots` query has no variables.
### Return Type
Recall that executing the `ListActiveTimeSlots` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListActiveTimeSlotsData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListActiveTimeSlotsData {
  timeSlots: ({
    id: UUIDString;
    startTime: string;
    endTime: string;
    displayOrder: number;
    isActive: boolean;
  } & TimeSlot_Key)[];
}
```
### Using `ListActiveTimeSlots`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listActiveTimeSlots } from '@firebasegen/example-connector';


// Call the `listActiveTimeSlots()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listActiveTimeSlots();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listActiveTimeSlots(dataConnect);

console.log(data.timeSlots);

// Or, you can use the `Promise` API.
listActiveTimeSlots().then((response) => {
  const data = response.data;
  console.log(data.timeSlots);
});
```

### Using `ListActiveTimeSlots`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listActiveTimeSlotsRef } from '@firebasegen/example-connector';


// Call the `listActiveTimeSlotsRef()` function to get a reference to the query.
const ref = listActiveTimeSlotsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listActiveTimeSlotsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.timeSlots);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.timeSlots);
});
```

## GetAvailabilityData
You can execute the `GetAvailabilityData` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getAvailabilityData(vars: GetAvailabilityDataVariables, options?: ExecuteQueryOptions): QueryPromise<GetAvailabilityDataData, GetAvailabilityDataVariables>;

interface GetAvailabilityDataRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetAvailabilityDataVariables): QueryRef<GetAvailabilityDataData, GetAvailabilityDataVariables>;
}
export const getAvailabilityDataRef: GetAvailabilityDataRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getAvailabilityData(dc: DataConnect, vars: GetAvailabilityDataVariables, options?: ExecuteQueryOptions): QueryPromise<GetAvailabilityDataData, GetAvailabilityDataVariables>;

interface GetAvailabilityDataRef {
  ...
  (dc: DataConnect, vars: GetAvailabilityDataVariables): QueryRef<GetAvailabilityDataData, GetAvailabilityDataVariables>;
}
export const getAvailabilityDataRef: GetAvailabilityDataRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getAvailabilityDataRef:
```typescript
const name = getAvailabilityDataRef.operationName;
console.log(name);
```

### Variables
The `GetAvailabilityData` query requires an argument of type `GetAvailabilityDataVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetAvailabilityDataVariables {
  bookingDate: DateString;
}
```
### Return Type
Recall that executing the `GetAvailabilityData` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetAvailabilityDataData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetAvailabilityDataData {
  courts: ({
    id: UUIDString;
    name: string;
    description?: string | null;
    status: string;
    createdAt: TimestampString;
    updatedAt: TimestampString;
  } & Court_Key)[];
  timeSlots: ({
    id: UUIDString;
    startTime: string;
    endTime: string;
    displayOrder: number;
    isActive: boolean;
  } & TimeSlot_Key)[];
  bookings: ({
    id: UUIDString;
    bookingDate: DateString;
    bookingType: string;
    status: string;
    bookedAt: TimestampString;
    cancelledAt?: TimestampString | null;
    createdAt: TimestampString;
    court: {
      id: UUIDString;
    } & Court_Key;
    timeSlot: {
      id: UUIDString;
    } & TimeSlot_Key;
    createdBy: {
      id: UUIDString;
      firebaseUid: string;
      fullName: string;
    } & User_Key;
    coach?: {
      id: UUIDString;
      fullName: string;
    } & User_Key;
    bookingPlayers_on_booking: ({
      id: UUIDString;
      playerPosition: number;
      joinedAt: TimestampString;
      user: {
        id: UUIDString;
        firebaseUid: string;
        fullName: string;
      } & User_Key;
    } & BookingPlayer_Key)[];
  } & Booking_Key)[];
}
```
### Using `GetAvailabilityData`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getAvailabilityData, GetAvailabilityDataVariables } from '@firebasegen/example-connector';

// The `GetAvailabilityData` query requires an argument of type `GetAvailabilityDataVariables`:
const getAvailabilityDataVars: GetAvailabilityDataVariables = {
  bookingDate: ..., 
};

// Call the `getAvailabilityData()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getAvailabilityData(getAvailabilityDataVars);
// Variables can be defined inline as well.
const { data } = await getAvailabilityData({ bookingDate: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getAvailabilityData(dataConnect, getAvailabilityDataVars);

console.log(data.courts);
console.log(data.timeSlots);
console.log(data.bookings);

// Or, you can use the `Promise` API.
getAvailabilityData(getAvailabilityDataVars).then((response) => {
  const data = response.data;
  console.log(data.courts);
  console.log(data.timeSlots);
  console.log(data.bookings);
});
```

### Using `GetAvailabilityData`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getAvailabilityDataRef, GetAvailabilityDataVariables } from '@firebasegen/example-connector';

// The `GetAvailabilityData` query requires an argument of type `GetAvailabilityDataVariables`:
const getAvailabilityDataVars: GetAvailabilityDataVariables = {
  bookingDate: ..., 
};

// Call the `getAvailabilityDataRef()` function to get a reference to the query.
const ref = getAvailabilityDataRef(getAvailabilityDataVars);
// Variables can be defined inline as well.
const ref = getAvailabilityDataRef({ bookingDate: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getAvailabilityDataRef(dataConnect, getAvailabilityDataVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.courts);
console.log(data.timeSlots);
console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.courts);
  console.log(data.timeSlots);
  console.log(data.bookings);
});
```

## GetBookingsByStatus
You can execute the `GetBookingsByStatus` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getBookingsByStatus(vars: GetBookingsByStatusVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByStatusData, GetBookingsByStatusVariables>;

interface GetBookingsByStatusRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsByStatusVariables): QueryRef<GetBookingsByStatusData, GetBookingsByStatusVariables>;
}
export const getBookingsByStatusRef: GetBookingsByStatusRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getBookingsByStatus(dc: DataConnect, vars: GetBookingsByStatusVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByStatusData, GetBookingsByStatusVariables>;

interface GetBookingsByStatusRef {
  ...
  (dc: DataConnect, vars: GetBookingsByStatusVariables): QueryRef<GetBookingsByStatusData, GetBookingsByStatusVariables>;
}
export const getBookingsByStatusRef: GetBookingsByStatusRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getBookingsByStatusRef:
```typescript
const name = getBookingsByStatusRef.operationName;
console.log(name);
```

### Variables
The `GetBookingsByStatus` query requires an argument of type `GetBookingsByStatusVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetBookingsByStatusVariables {
  status: string;
}
```
### Return Type
Recall that executing the `GetBookingsByStatus` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetBookingsByStatusData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetBookingsByStatusData {
  bookings: ({
    id: UUIDString;
    bookingDate: DateString;
    bookingType: string;
    status: string;
    bookedAt: TimestampString;
    cancelledAt?: TimestampString | null;
    createdAt: TimestampString;
    court: {
      id: UUIDString;
      name: string;
    } & Court_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
    } & TimeSlot_Key;
    createdBy: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    coach?: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    bookingPlayers_on_booking: ({
      id: UUIDString;
      playerPosition: number;
      joinedAt: TimestampString;
      user: {
        id: UUIDString;
        fullName: string;
        email: string;
      } & User_Key;
    } & BookingPlayer_Key)[];
  } & Booking_Key)[];
}
```
### Using `GetBookingsByStatus`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getBookingsByStatus, GetBookingsByStatusVariables } from '@firebasegen/example-connector';

// The `GetBookingsByStatus` query requires an argument of type `GetBookingsByStatusVariables`:
const getBookingsByStatusVars: GetBookingsByStatusVariables = {
  status: ..., 
};

// Call the `getBookingsByStatus()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getBookingsByStatus(getBookingsByStatusVars);
// Variables can be defined inline as well.
const { data } = await getBookingsByStatus({ status: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getBookingsByStatus(dataConnect, getBookingsByStatusVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getBookingsByStatus(getBookingsByStatusVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetBookingsByStatus`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getBookingsByStatusRef, GetBookingsByStatusVariables } from '@firebasegen/example-connector';

// The `GetBookingsByStatus` query requires an argument of type `GetBookingsByStatusVariables`:
const getBookingsByStatusVars: GetBookingsByStatusVariables = {
  status: ..., 
};

// Call the `getBookingsByStatusRef()` function to get a reference to the query.
const ref = getBookingsByStatusRef(getBookingsByStatusVars);
// Variables can be defined inline as well.
const ref = getBookingsByStatusRef({ status: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getBookingsByStatusRef(dataConnect, getBookingsByStatusVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetBookingsForDate
You can execute the `GetBookingsForDate` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getBookingsForDate(vars: GetBookingsForDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsForDateData, GetBookingsForDateVariables>;

interface GetBookingsForDateRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsForDateVariables): QueryRef<GetBookingsForDateData, GetBookingsForDateVariables>;
}
export const getBookingsForDateRef: GetBookingsForDateRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getBookingsForDate(dc: DataConnect, vars: GetBookingsForDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsForDateData, GetBookingsForDateVariables>;

interface GetBookingsForDateRef {
  ...
  (dc: DataConnect, vars: GetBookingsForDateVariables): QueryRef<GetBookingsForDateData, GetBookingsForDateVariables>;
}
export const getBookingsForDateRef: GetBookingsForDateRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getBookingsForDateRef:
```typescript
const name = getBookingsForDateRef.operationName;
console.log(name);
```

### Variables
The `GetBookingsForDate` query requires an argument of type `GetBookingsForDateVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetBookingsForDateVariables {
  bookingDate: DateString;
}
```
### Return Type
Recall that executing the `GetBookingsForDate` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetBookingsForDateData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetBookingsForDateData {
  bookings: ({
    id: UUIDString;
    bookingDate: DateString;
    bookingType: string;
    status: string;
    bookedAt: TimestampString;
    cancelledAt?: TimestampString | null;
    createdAt: TimestampString;
    court: {
      id: UUIDString;
      name: string;
    } & Court_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
    } & TimeSlot_Key;
    createdBy: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    coach?: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    bookingPlayers_on_booking: ({
      id: UUIDString;
      playerPosition: number;
      joinedAt: TimestampString;
      user: {
        id: UUIDString;
        fullName: string;
        email: string;
      } & User_Key;
    } & BookingPlayer_Key)[];
  } & Booking_Key)[];
}
```
### Using `GetBookingsForDate`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getBookingsForDate, GetBookingsForDateVariables } from '@firebasegen/example-connector';

// The `GetBookingsForDate` query requires an argument of type `GetBookingsForDateVariables`:
const getBookingsForDateVars: GetBookingsForDateVariables = {
  bookingDate: ..., 
};

// Call the `getBookingsForDate()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getBookingsForDate(getBookingsForDateVars);
// Variables can be defined inline as well.
const { data } = await getBookingsForDate({ bookingDate: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getBookingsForDate(dataConnect, getBookingsForDateVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getBookingsForDate(getBookingsForDateVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetBookingsForDate`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getBookingsForDateRef, GetBookingsForDateVariables } from '@firebasegen/example-connector';

// The `GetBookingsForDate` query requires an argument of type `GetBookingsForDateVariables`:
const getBookingsForDateVars: GetBookingsForDateVariables = {
  bookingDate: ..., 
};

// Call the `getBookingsForDateRef()` function to get a reference to the query.
const ref = getBookingsForDateRef(getBookingsForDateVars);
// Variables can be defined inline as well.
const ref = getBookingsForDateRef({ bookingDate: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getBookingsForDateRef(dataConnect, getBookingsForDateVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetBookingById
You can execute the `GetBookingById` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getBookingById(vars: GetBookingByIdVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingByIdData, GetBookingByIdVariables>;

interface GetBookingByIdRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingByIdVariables): QueryRef<GetBookingByIdData, GetBookingByIdVariables>;
}
export const getBookingByIdRef: GetBookingByIdRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getBookingById(dc: DataConnect, vars: GetBookingByIdVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingByIdData, GetBookingByIdVariables>;

interface GetBookingByIdRef {
  ...
  (dc: DataConnect, vars: GetBookingByIdVariables): QueryRef<GetBookingByIdData, GetBookingByIdVariables>;
}
export const getBookingByIdRef: GetBookingByIdRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getBookingByIdRef:
```typescript
const name = getBookingByIdRef.operationName;
console.log(name);
```

### Variables
The `GetBookingById` query requires an argument of type `GetBookingByIdVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetBookingByIdVariables {
  id: UUIDString;
}
```
### Return Type
Recall that executing the `GetBookingById` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetBookingByIdData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetBookingByIdData {
  booking?: {
    id: UUIDString;
    bookingDate: DateString;
    bookingType: string;
    status: string;
    bookedAt: TimestampString;
    cancelledAt?: TimestampString | null;
    createdAt: TimestampString;
    court: {
      id: UUIDString;
      name: string;
    } & Court_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
    } & TimeSlot_Key;
    createdBy: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    coach?: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    bookingPlayers_on_booking: ({
      id: UUIDString;
      playerPosition: number;
      joinedAt: TimestampString;
      user: {
        id: UUIDString;
        fullName: string;
        email: string;
      } & User_Key;
    } & BookingPlayer_Key)[];
  } & Booking_Key;
}
```
### Using `GetBookingById`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getBookingById, GetBookingByIdVariables } from '@firebasegen/example-connector';

// The `GetBookingById` query requires an argument of type `GetBookingByIdVariables`:
const getBookingByIdVars: GetBookingByIdVariables = {
  id: ..., 
};

// Call the `getBookingById()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getBookingById(getBookingByIdVars);
// Variables can be defined inline as well.
const { data } = await getBookingById({ id: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getBookingById(dataConnect, getBookingByIdVars);

console.log(data.booking);

// Or, you can use the `Promise` API.
getBookingById(getBookingByIdVars).then((response) => {
  const data = response.data;
  console.log(data.booking);
});
```

### Using `GetBookingById`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getBookingByIdRef, GetBookingByIdVariables } from '@firebasegen/example-connector';

// The `GetBookingById` query requires an argument of type `GetBookingByIdVariables`:
const getBookingByIdVars: GetBookingByIdVariables = {
  id: ..., 
};

// Call the `getBookingByIdRef()` function to get a reference to the query.
const ref = getBookingByIdRef(getBookingByIdVars);
// Variables can be defined inline as well.
const ref = getBookingByIdRef({ id: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getBookingByIdRef(dataConnect, getBookingByIdVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.booking);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.booking);
});
```

## GetBookingsForTimeSlot
You can execute the `GetBookingsForTimeSlot` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getBookingsForTimeSlot(vars: GetBookingsForTimeSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsForTimeSlotData, GetBookingsForTimeSlotVariables>;

interface GetBookingsForTimeSlotRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsForTimeSlotVariables): QueryRef<GetBookingsForTimeSlotData, GetBookingsForTimeSlotVariables>;
}
export const getBookingsForTimeSlotRef: GetBookingsForTimeSlotRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getBookingsForTimeSlot(dc: DataConnect, vars: GetBookingsForTimeSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsForTimeSlotData, GetBookingsForTimeSlotVariables>;

interface GetBookingsForTimeSlotRef {
  ...
  (dc: DataConnect, vars: GetBookingsForTimeSlotVariables): QueryRef<GetBookingsForTimeSlotData, GetBookingsForTimeSlotVariables>;
}
export const getBookingsForTimeSlotRef: GetBookingsForTimeSlotRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getBookingsForTimeSlotRef:
```typescript
const name = getBookingsForTimeSlotRef.operationName;
console.log(name);
```

### Variables
The `GetBookingsForTimeSlot` query requires an argument of type `GetBookingsForTimeSlotVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetBookingsForTimeSlotVariables {
  bookingDate: DateString;
  timeSlotId: UUIDString;
}
```
### Return Type
Recall that executing the `GetBookingsForTimeSlot` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetBookingsForTimeSlotData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetBookingsForTimeSlotData {
  bookings: ({
    id: UUIDString;
    createdBy: {
      id: UUIDString;
    } & User_Key;
    bookingPlayers_on_booking: ({
      user: {
        id: UUIDString;
      } & User_Key;
    })[];
  } & Booking_Key)[];
}
```
### Using `GetBookingsForTimeSlot`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getBookingsForTimeSlot, GetBookingsForTimeSlotVariables } from '@firebasegen/example-connector';

// The `GetBookingsForTimeSlot` query requires an argument of type `GetBookingsForTimeSlotVariables`:
const getBookingsForTimeSlotVars: GetBookingsForTimeSlotVariables = {
  bookingDate: ..., 
  timeSlotId: ..., 
};

// Call the `getBookingsForTimeSlot()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getBookingsForTimeSlot(getBookingsForTimeSlotVars);
// Variables can be defined inline as well.
const { data } = await getBookingsForTimeSlot({ bookingDate: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getBookingsForTimeSlot(dataConnect, getBookingsForTimeSlotVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getBookingsForTimeSlot(getBookingsForTimeSlotVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetBookingsForTimeSlot`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getBookingsForTimeSlotRef, GetBookingsForTimeSlotVariables } from '@firebasegen/example-connector';

// The `GetBookingsForTimeSlot` query requires an argument of type `GetBookingsForTimeSlotVariables`:
const getBookingsForTimeSlotVars: GetBookingsForTimeSlotVariables = {
  bookingDate: ..., 
  timeSlotId: ..., 
};

// Call the `getBookingsForTimeSlotRef()` function to get a reference to the query.
const ref = getBookingsForTimeSlotRef(getBookingsForTimeSlotVars);
// Variables can be defined inline as well.
const ref = getBookingsForTimeSlotRef({ bookingDate: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getBookingsForTimeSlotRef(dataConnect, getBookingsForTimeSlotVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetTeamPracticeBookingsByCoachDate
You can execute the `GetTeamPracticeBookingsByCoachDate` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getTeamPracticeBookingsByCoachDate(vars: GetTeamPracticeBookingsByCoachDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetTeamPracticeBookingsByCoachDateData, GetTeamPracticeBookingsByCoachDateVariables>;

interface GetTeamPracticeBookingsByCoachDateRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetTeamPracticeBookingsByCoachDateVariables): QueryRef<GetTeamPracticeBookingsByCoachDateData, GetTeamPracticeBookingsByCoachDateVariables>;
}
export const getTeamPracticeBookingsByCoachDateRef: GetTeamPracticeBookingsByCoachDateRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getTeamPracticeBookingsByCoachDate(dc: DataConnect, vars: GetTeamPracticeBookingsByCoachDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetTeamPracticeBookingsByCoachDateData, GetTeamPracticeBookingsByCoachDateVariables>;

interface GetTeamPracticeBookingsByCoachDateRef {
  ...
  (dc: DataConnect, vars: GetTeamPracticeBookingsByCoachDateVariables): QueryRef<GetTeamPracticeBookingsByCoachDateData, GetTeamPracticeBookingsByCoachDateVariables>;
}
export const getTeamPracticeBookingsByCoachDateRef: GetTeamPracticeBookingsByCoachDateRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getTeamPracticeBookingsByCoachDateRef:
```typescript
const name = getTeamPracticeBookingsByCoachDateRef.operationName;
console.log(name);
```

### Variables
The `GetTeamPracticeBookingsByCoachDate` query requires an argument of type `GetTeamPracticeBookingsByCoachDateVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetTeamPracticeBookingsByCoachDateVariables {
  coachId: UUIDString;
  bookingDate: DateString;
}
```
### Return Type
Recall that executing the `GetTeamPracticeBookingsByCoachDate` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetTeamPracticeBookingsByCoachDateData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetTeamPracticeBookingsByCoachDateData {
  bookings: ({
    id: UUIDString;
    bookingDate: DateString;
    bookingType: string;
    status: string;
    bookedAt: TimestampString;
    cancelledAt?: TimestampString | null;
    createdAt: TimestampString;
    court: {
      id: UUIDString;
      name: string;
    } & Court_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
    } & TimeSlot_Key;
    createdBy: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    coach?: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    bookingPlayers_on_booking: ({
      id: UUIDString;
      playerPosition: number;
      joinedAt: TimestampString;
      user: {
        id: UUIDString;
        fullName: string;
        email: string;
      } & User_Key;
    } & BookingPlayer_Key)[];
  } & Booking_Key)[];
}
```
### Using `GetTeamPracticeBookingsByCoachDate`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getTeamPracticeBookingsByCoachDate, GetTeamPracticeBookingsByCoachDateVariables } from '@firebasegen/example-connector';

// The `GetTeamPracticeBookingsByCoachDate` query requires an argument of type `GetTeamPracticeBookingsByCoachDateVariables`:
const getTeamPracticeBookingsByCoachDateVars: GetTeamPracticeBookingsByCoachDateVariables = {
  coachId: ..., 
  bookingDate: ..., 
};

// Call the `getTeamPracticeBookingsByCoachDate()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getTeamPracticeBookingsByCoachDate(getTeamPracticeBookingsByCoachDateVars);
// Variables can be defined inline as well.
const { data } = await getTeamPracticeBookingsByCoachDate({ coachId: ..., bookingDate: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getTeamPracticeBookingsByCoachDate(dataConnect, getTeamPracticeBookingsByCoachDateVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getTeamPracticeBookingsByCoachDate(getTeamPracticeBookingsByCoachDateVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetTeamPracticeBookingsByCoachDate`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getTeamPracticeBookingsByCoachDateRef, GetTeamPracticeBookingsByCoachDateVariables } from '@firebasegen/example-connector';

// The `GetTeamPracticeBookingsByCoachDate` query requires an argument of type `GetTeamPracticeBookingsByCoachDateVariables`:
const getTeamPracticeBookingsByCoachDateVars: GetTeamPracticeBookingsByCoachDateVariables = {
  coachId: ..., 
  bookingDate: ..., 
};

// Call the `getTeamPracticeBookingsByCoachDateRef()` function to get a reference to the query.
const ref = getTeamPracticeBookingsByCoachDateRef(getTeamPracticeBookingsByCoachDateVars);
// Variables can be defined inline as well.
const ref = getTeamPracticeBookingsByCoachDateRef({ coachId: ..., bookingDate: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getTeamPracticeBookingsByCoachDateRef(dataConnect, getTeamPracticeBookingsByCoachDateVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetBookingsByUser
You can execute the `GetBookingsByUser` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getBookingsByUser(vars: GetBookingsByUserVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByUserData, GetBookingsByUserVariables>;

interface GetBookingsByUserRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsByUserVariables): QueryRef<GetBookingsByUserData, GetBookingsByUserVariables>;
}
export const getBookingsByUserRef: GetBookingsByUserRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getBookingsByUser(dc: DataConnect, vars: GetBookingsByUserVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByUserData, GetBookingsByUserVariables>;

interface GetBookingsByUserRef {
  ...
  (dc: DataConnect, vars: GetBookingsByUserVariables): QueryRef<GetBookingsByUserData, GetBookingsByUserVariables>;
}
export const getBookingsByUserRef: GetBookingsByUserRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getBookingsByUserRef:
```typescript
const name = getBookingsByUserRef.operationName;
console.log(name);
```

### Variables
The `GetBookingsByUser` query requires an argument of type `GetBookingsByUserVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetBookingsByUserVariables {
  userId: UUIDString;
  status?: string | null;
}
```
### Return Type
Recall that executing the `GetBookingsByUser` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetBookingsByUserData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetBookingsByUserData {
  bookings: ({
    id: UUIDString;
    bookingDate: DateString;
    bookingType: string;
    status: string;
    bookedAt: TimestampString;
    cancelledAt?: TimestampString | null;
    createdAt: TimestampString;
    court: {
      id: UUIDString;
      name: string;
    } & Court_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
    } & TimeSlot_Key;
    coach?: {
      id: UUIDString;
      fullName: string;
    } & User_Key;
    bookingPlayers_on_booking: ({
      id: UUIDString;
      playerPosition: number;
      joinedAt: TimestampString;
      user: {
        id: UUIDString;
        fullName: string;
      } & User_Key;
    } & BookingPlayer_Key)[];
  } & Booking_Key)[];
}
```
### Using `GetBookingsByUser`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getBookingsByUser, GetBookingsByUserVariables } from '@firebasegen/example-connector';

// The `GetBookingsByUser` query requires an argument of type `GetBookingsByUserVariables`:
const getBookingsByUserVars: GetBookingsByUserVariables = {
  userId: ..., 
  status: ..., // optional
};

// Call the `getBookingsByUser()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getBookingsByUser(getBookingsByUserVars);
// Variables can be defined inline as well.
const { data } = await getBookingsByUser({ userId: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getBookingsByUser(dataConnect, getBookingsByUserVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getBookingsByUser(getBookingsByUserVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetBookingsByUser`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getBookingsByUserRef, GetBookingsByUserVariables } from '@firebasegen/example-connector';

// The `GetBookingsByUser` query requires an argument of type `GetBookingsByUserVariables`:
const getBookingsByUserVars: GetBookingsByUserVariables = {
  userId: ..., 
  status: ..., // optional
};

// Call the `getBookingsByUserRef()` function to get a reference to the query.
const ref = getBookingsByUserRef(getBookingsByUserVars);
// Variables can be defined inline as well.
const ref = getBookingsByUserRef({ userId: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getBookingsByUserRef(dataConnect, getBookingsByUserVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetBookingPlayers
You can execute the `GetBookingPlayers` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getBookingPlayers(vars: GetBookingPlayersVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingPlayersData, GetBookingPlayersVariables>;

interface GetBookingPlayersRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingPlayersVariables): QueryRef<GetBookingPlayersData, GetBookingPlayersVariables>;
}
export const getBookingPlayersRef: GetBookingPlayersRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getBookingPlayers(dc: DataConnect, vars: GetBookingPlayersVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingPlayersData, GetBookingPlayersVariables>;

interface GetBookingPlayersRef {
  ...
  (dc: DataConnect, vars: GetBookingPlayersVariables): QueryRef<GetBookingPlayersData, GetBookingPlayersVariables>;
}
export const getBookingPlayersRef: GetBookingPlayersRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getBookingPlayersRef:
```typescript
const name = getBookingPlayersRef.operationName;
console.log(name);
```

### Variables
The `GetBookingPlayers` query requires an argument of type `GetBookingPlayersVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetBookingPlayersVariables {
  bookingId: UUIDString;
}
```
### Return Type
Recall that executing the `GetBookingPlayers` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetBookingPlayersData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetBookingPlayersData {
  bookingPlayers: ({
    id: UUIDString;
    playerPosition: number;
    joinedAt: TimestampString;
    user: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
  } & BookingPlayer_Key)[];
}
```
### Using `GetBookingPlayers`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getBookingPlayers, GetBookingPlayersVariables } from '@firebasegen/example-connector';

// The `GetBookingPlayers` query requires an argument of type `GetBookingPlayersVariables`:
const getBookingPlayersVars: GetBookingPlayersVariables = {
  bookingId: ..., 
};

// Call the `getBookingPlayers()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getBookingPlayers(getBookingPlayersVars);
// Variables can be defined inline as well.
const { data } = await getBookingPlayers({ bookingId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getBookingPlayers(dataConnect, getBookingPlayersVars);

console.log(data.bookingPlayers);

// Or, you can use the `Promise` API.
getBookingPlayers(getBookingPlayersVars).then((response) => {
  const data = response.data;
  console.log(data.bookingPlayers);
});
```

### Using `GetBookingPlayers`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getBookingPlayersRef, GetBookingPlayersVariables } from '@firebasegen/example-connector';

// The `GetBookingPlayers` query requires an argument of type `GetBookingPlayersVariables`:
const getBookingPlayersVars: GetBookingPlayersVariables = {
  bookingId: ..., 
};

// Call the `getBookingPlayersRef()` function to get a reference to the query.
const ref = getBookingPlayersRef(getBookingPlayersVars);
// Variables can be defined inline as well.
const ref = getBookingPlayersRef({ bookingId: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getBookingPlayersRef(dataConnect, getBookingPlayersVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookingPlayers);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookingPlayers);
});
```

## GetAvailableCoaches
You can execute the `GetAvailableCoaches` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getAvailableCoaches(vars: GetAvailableCoachesVariables, options?: ExecuteQueryOptions): QueryPromise<GetAvailableCoachesData, GetAvailableCoachesVariables>;

interface GetAvailableCoachesRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetAvailableCoachesVariables): QueryRef<GetAvailableCoachesData, GetAvailableCoachesVariables>;
}
export const getAvailableCoachesRef: GetAvailableCoachesRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getAvailableCoaches(dc: DataConnect, vars: GetAvailableCoachesVariables, options?: ExecuteQueryOptions): QueryPromise<GetAvailableCoachesData, GetAvailableCoachesVariables>;

interface GetAvailableCoachesRef {
  ...
  (dc: DataConnect, vars: GetAvailableCoachesVariables): QueryRef<GetAvailableCoachesData, GetAvailableCoachesVariables>;
}
export const getAvailableCoachesRef: GetAvailableCoachesRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getAvailableCoachesRef:
```typescript
const name = getAvailableCoachesRef.operationName;
console.log(name);
```

### Variables
The `GetAvailableCoaches` query requires an argument of type `GetAvailableCoachesVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetAvailableCoachesVariables {
  availabilityDate: DateString;
  timeSlotId: UUIDString;
}
```
### Return Type
Recall that executing the `GetAvailableCoaches` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetAvailableCoachesData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetAvailableCoachesData {
  coachAvailabilities: ({
    availabilityDate: DateString;
    createdAt: TimestampString;
    coachId: UUIDString;
    timeSlotId: UUIDString;
    coach: {
      id: UUIDString;
      fullName: string;
      email: string;
    } & User_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
    } & TimeSlot_Key;
  } & CoachAvailability_Key)[];
}
```
### Using `GetAvailableCoaches`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getAvailableCoaches, GetAvailableCoachesVariables } from '@firebasegen/example-connector';

// The `GetAvailableCoaches` query requires an argument of type `GetAvailableCoachesVariables`:
const getAvailableCoachesVars: GetAvailableCoachesVariables = {
  availabilityDate: ..., 
  timeSlotId: ..., 
};

// Call the `getAvailableCoaches()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getAvailableCoaches(getAvailableCoachesVars);
// Variables can be defined inline as well.
const { data } = await getAvailableCoaches({ availabilityDate: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getAvailableCoaches(dataConnect, getAvailableCoachesVars);

console.log(data.coachAvailabilities);

// Or, you can use the `Promise` API.
getAvailableCoaches(getAvailableCoachesVars).then((response) => {
  const data = response.data;
  console.log(data.coachAvailabilities);
});
```

### Using `GetAvailableCoaches`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getAvailableCoachesRef, GetAvailableCoachesVariables } from '@firebasegen/example-connector';

// The `GetAvailableCoaches` query requires an argument of type `GetAvailableCoachesVariables`:
const getAvailableCoachesVars: GetAvailableCoachesVariables = {
  availabilityDate: ..., 
  timeSlotId: ..., 
};

// Call the `getAvailableCoachesRef()` function to get a reference to the query.
const ref = getAvailableCoachesRef(getAvailableCoachesVars);
// Variables can be defined inline as well.
const ref = getAvailableCoachesRef({ availabilityDate: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getAvailableCoachesRef(dataConnect, getAvailableCoachesVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.coachAvailabilities);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.coachAvailabilities);
});
```

## IsCoachAvailable
You can execute the `IsCoachAvailable` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
isCoachAvailable(vars: IsCoachAvailableVariables, options?: ExecuteQueryOptions): QueryPromise<IsCoachAvailableData, IsCoachAvailableVariables>;

interface IsCoachAvailableRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: IsCoachAvailableVariables): QueryRef<IsCoachAvailableData, IsCoachAvailableVariables>;
}
export const isCoachAvailableRef: IsCoachAvailableRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
isCoachAvailable(dc: DataConnect, vars: IsCoachAvailableVariables, options?: ExecuteQueryOptions): QueryPromise<IsCoachAvailableData, IsCoachAvailableVariables>;

interface IsCoachAvailableRef {
  ...
  (dc: DataConnect, vars: IsCoachAvailableVariables): QueryRef<IsCoachAvailableData, IsCoachAvailableVariables>;
}
export const isCoachAvailableRef: IsCoachAvailableRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the isCoachAvailableRef:
```typescript
const name = isCoachAvailableRef.operationName;
console.log(name);
```

### Variables
The `IsCoachAvailable` query requires an argument of type `IsCoachAvailableVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface IsCoachAvailableVariables {
  availabilityDate: DateString;
  timeSlotId: UUIDString;
  coachId: UUIDString;
}
```
### Return Type
Recall that executing the `IsCoachAvailable` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `IsCoachAvailableData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface IsCoachAvailableData {
  coachAvailabilities: ({
    coachId: UUIDString;
  })[];
}
```
### Using `IsCoachAvailable`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, isCoachAvailable, IsCoachAvailableVariables } from '@firebasegen/example-connector';

// The `IsCoachAvailable` query requires an argument of type `IsCoachAvailableVariables`:
const isCoachAvailableVars: IsCoachAvailableVariables = {
  availabilityDate: ..., 
  timeSlotId: ..., 
  coachId: ..., 
};

// Call the `isCoachAvailable()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await isCoachAvailable(isCoachAvailableVars);
// Variables can be defined inline as well.
const { data } = await isCoachAvailable({ availabilityDate: ..., timeSlotId: ..., coachId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await isCoachAvailable(dataConnect, isCoachAvailableVars);

console.log(data.coachAvailabilities);

// Or, you can use the `Promise` API.
isCoachAvailable(isCoachAvailableVars).then((response) => {
  const data = response.data;
  console.log(data.coachAvailabilities);
});
```

### Using `IsCoachAvailable`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, isCoachAvailableRef, IsCoachAvailableVariables } from '@firebasegen/example-connector';

// The `IsCoachAvailable` query requires an argument of type `IsCoachAvailableVariables`:
const isCoachAvailableVars: IsCoachAvailableVariables = {
  availabilityDate: ..., 
  timeSlotId: ..., 
  coachId: ..., 
};

// Call the `isCoachAvailableRef()` function to get a reference to the query.
const ref = isCoachAvailableRef(isCoachAvailableVars);
// Variables can be defined inline as well.
const ref = isCoachAvailableRef({ availabilityDate: ..., timeSlotId: ..., coachId: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = isCoachAvailableRef(dataConnect, isCoachAvailableVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.coachAvailabilities);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.coachAvailabilities);
});
```

## ListAllUsers
You can execute the `ListAllUsers` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
listAllUsers(options?: ExecuteQueryOptions): QueryPromise<ListAllUsersData, undefined>;

interface ListAllUsersRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListAllUsersData, undefined>;
}
export const listAllUsersRef: ListAllUsersRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listAllUsers(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListAllUsersData, undefined>;

interface ListAllUsersRef {
  ...
  (dc: DataConnect): QueryRef<ListAllUsersData, undefined>;
}
export const listAllUsersRef: ListAllUsersRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listAllUsersRef:
```typescript
const name = listAllUsersRef.operationName;
console.log(name);
```

### Variables
The `ListAllUsers` query has no variables.
### Return Type
Recall that executing the `ListAllUsers` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListAllUsersData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListAllUsersData {
  users: ({
    id: UUIDString;
    firebaseUid: string;
    studentNumber?: string | null;
    fullName: string;
    email: string;
    role: string;
    status: string;
    createdAt: TimestampString;
    updatedAt: TimestampString;
  } & User_Key)[];
}
```
### Using `ListAllUsers`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listAllUsers } from '@firebasegen/example-connector';


// Call the `listAllUsers()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listAllUsers();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listAllUsers(dataConnect);

console.log(data.users);

// Or, you can use the `Promise` API.
listAllUsers().then((response) => {
  const data = response.data;
  console.log(data.users);
});
```

### Using `ListAllUsers`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listAllUsersRef } from '@firebasegen/example-connector';


// Call the `listAllUsersRef()` function to get a reference to the query.
const ref = listAllUsersRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listAllUsersRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.users);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.users);
});
```

## ListAllCourts
You can execute the `ListAllCourts` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
listAllCourts(options?: ExecuteQueryOptions): QueryPromise<ListAllCourtsData, undefined>;

interface ListAllCourtsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListAllCourtsData, undefined>;
}
export const listAllCourtsRef: ListAllCourtsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listAllCourts(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListAllCourtsData, undefined>;

interface ListAllCourtsRef {
  ...
  (dc: DataConnect): QueryRef<ListAllCourtsData, undefined>;
}
export const listAllCourtsRef: ListAllCourtsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listAllCourtsRef:
```typescript
const name = listAllCourtsRef.operationName;
console.log(name);
```

### Variables
The `ListAllCourts` query has no variables.
### Return Type
Recall that executing the `ListAllCourts` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListAllCourtsData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListAllCourtsData {
  courts: ({
    id: UUIDString;
    name: string;
    description?: string | null;
    status: string;
    createdAt: TimestampString;
    updatedAt: TimestampString;
  } & Court_Key)[];
}
```
### Using `ListAllCourts`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listAllCourts } from '@firebasegen/example-connector';


// Call the `listAllCourts()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listAllCourts();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listAllCourts(dataConnect);

console.log(data.courts);

// Or, you can use the `Promise` API.
listAllCourts().then((response) => {
  const data = response.data;
  console.log(data.courts);
});
```

### Using `ListAllCourts`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listAllCourtsRef } from '@firebasegen/example-connector';


// Call the `listAllCourtsRef()` function to get a reference to the query.
const ref = listAllCourtsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listAllCourtsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.courts);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.courts);
});
```

## GetCoachAvailabilityForDate
You can execute the `GetCoachAvailabilityForDate` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getCoachAvailabilityForDate(vars: GetCoachAvailabilityForDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetCoachAvailabilityForDateData, GetCoachAvailabilityForDateVariables>;

interface GetCoachAvailabilityForDateRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetCoachAvailabilityForDateVariables): QueryRef<GetCoachAvailabilityForDateData, GetCoachAvailabilityForDateVariables>;
}
export const getCoachAvailabilityForDateRef: GetCoachAvailabilityForDateRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getCoachAvailabilityForDate(dc: DataConnect, vars: GetCoachAvailabilityForDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetCoachAvailabilityForDateData, GetCoachAvailabilityForDateVariables>;

interface GetCoachAvailabilityForDateRef {
  ...
  (dc: DataConnect, vars: GetCoachAvailabilityForDateVariables): QueryRef<GetCoachAvailabilityForDateData, GetCoachAvailabilityForDateVariables>;
}
export const getCoachAvailabilityForDateRef: GetCoachAvailabilityForDateRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getCoachAvailabilityForDateRef:
```typescript
const name = getCoachAvailabilityForDateRef.operationName;
console.log(name);
```

### Variables
The `GetCoachAvailabilityForDate` query requires an argument of type `GetCoachAvailabilityForDateVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetCoachAvailabilityForDateVariables {
  coachId: UUIDString;
  availabilityDate: DateString;
}
```
### Return Type
Recall that executing the `GetCoachAvailabilityForDate` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetCoachAvailabilityForDateData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetCoachAvailabilityForDateData {
  coachAvailabilities: ({
    availabilityDate: DateString;
    createdAt: TimestampString;
    coachId: UUIDString;
    timeSlotId: UUIDString;
    coach: {
      id: UUIDString;
      fullName: string;
    } & User_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
    } & TimeSlot_Key;
  } & CoachAvailability_Key)[];
}
```
### Using `GetCoachAvailabilityForDate`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getCoachAvailabilityForDate, GetCoachAvailabilityForDateVariables } from '@firebasegen/example-connector';

// The `GetCoachAvailabilityForDate` query requires an argument of type `GetCoachAvailabilityForDateVariables`:
const getCoachAvailabilityForDateVars: GetCoachAvailabilityForDateVariables = {
  coachId: ..., 
  availabilityDate: ..., 
};

// Call the `getCoachAvailabilityForDate()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getCoachAvailabilityForDate(getCoachAvailabilityForDateVars);
// Variables can be defined inline as well.
const { data } = await getCoachAvailabilityForDate({ coachId: ..., availabilityDate: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getCoachAvailabilityForDate(dataConnect, getCoachAvailabilityForDateVars);

console.log(data.coachAvailabilities);

// Or, you can use the `Promise` API.
getCoachAvailabilityForDate(getCoachAvailabilityForDateVars).then((response) => {
  const data = response.data;
  console.log(data.coachAvailabilities);
});
```

### Using `GetCoachAvailabilityForDate`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getCoachAvailabilityForDateRef, GetCoachAvailabilityForDateVariables } from '@firebasegen/example-connector';

// The `GetCoachAvailabilityForDate` query requires an argument of type `GetCoachAvailabilityForDateVariables`:
const getCoachAvailabilityForDateVars: GetCoachAvailabilityForDateVariables = {
  coachId: ..., 
  availabilityDate: ..., 
};

// Call the `getCoachAvailabilityForDateRef()` function to get a reference to the query.
const ref = getCoachAvailabilityForDateRef(getCoachAvailabilityForDateVars);
// Variables can be defined inline as well.
const ref = getCoachAvailabilityForDateRef({ coachId: ..., availabilityDate: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getCoachAvailabilityForDateRef(dataConnect, getCoachAvailabilityForDateVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.coachAvailabilities);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.coachAvailabilities);
});
```

## GetConfirmedBookingForCoachSlot
You can execute the `GetConfirmedBookingForCoachSlot` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getConfirmedBookingForCoachSlot(vars: GetConfirmedBookingForCoachSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingForCoachSlotData, GetConfirmedBookingForCoachSlotVariables>;

interface GetConfirmedBookingForCoachSlotRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetConfirmedBookingForCoachSlotVariables): QueryRef<GetConfirmedBookingForCoachSlotData, GetConfirmedBookingForCoachSlotVariables>;
}
export const getConfirmedBookingForCoachSlotRef: GetConfirmedBookingForCoachSlotRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getConfirmedBookingForCoachSlot(dc: DataConnect, vars: GetConfirmedBookingForCoachSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingForCoachSlotData, GetConfirmedBookingForCoachSlotVariables>;

interface GetConfirmedBookingForCoachSlotRef {
  ...
  (dc: DataConnect, vars: GetConfirmedBookingForCoachSlotVariables): QueryRef<GetConfirmedBookingForCoachSlotData, GetConfirmedBookingForCoachSlotVariables>;
}
export const getConfirmedBookingForCoachSlotRef: GetConfirmedBookingForCoachSlotRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getConfirmedBookingForCoachSlotRef:
```typescript
const name = getConfirmedBookingForCoachSlotRef.operationName;
console.log(name);
```

### Variables
The `GetConfirmedBookingForCoachSlot` query requires an argument of type `GetConfirmedBookingForCoachSlotVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetConfirmedBookingForCoachSlotVariables {
  coachId: UUIDString;
  bookingDate: DateString;
  timeSlotId: UUIDString;
}
```
### Return Type
Recall that executing the `GetConfirmedBookingForCoachSlot` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetConfirmedBookingForCoachSlotData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetConfirmedBookingForCoachSlotData {
  bookings: ({
    id: UUIDString;
  } & Booking_Key)[];
}
```
### Using `GetConfirmedBookingForCoachSlot`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getConfirmedBookingForCoachSlot, GetConfirmedBookingForCoachSlotVariables } from '@firebasegen/example-connector';

// The `GetConfirmedBookingForCoachSlot` query requires an argument of type `GetConfirmedBookingForCoachSlotVariables`:
const getConfirmedBookingForCoachSlotVars: GetConfirmedBookingForCoachSlotVariables = {
  coachId: ..., 
  bookingDate: ..., 
  timeSlotId: ..., 
};

// Call the `getConfirmedBookingForCoachSlot()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getConfirmedBookingForCoachSlot(getConfirmedBookingForCoachSlotVars);
// Variables can be defined inline as well.
const { data } = await getConfirmedBookingForCoachSlot({ coachId: ..., bookingDate: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getConfirmedBookingForCoachSlot(dataConnect, getConfirmedBookingForCoachSlotVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getConfirmedBookingForCoachSlot(getConfirmedBookingForCoachSlotVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetConfirmedBookingForCoachSlot`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getConfirmedBookingForCoachSlotRef, GetConfirmedBookingForCoachSlotVariables } from '@firebasegen/example-connector';

// The `GetConfirmedBookingForCoachSlot` query requires an argument of type `GetConfirmedBookingForCoachSlotVariables`:
const getConfirmedBookingForCoachSlotVars: GetConfirmedBookingForCoachSlotVariables = {
  coachId: ..., 
  bookingDate: ..., 
  timeSlotId: ..., 
};

// Call the `getConfirmedBookingForCoachSlotRef()` function to get a reference to the query.
const ref = getConfirmedBookingForCoachSlotRef(getConfirmedBookingForCoachSlotVars);
// Variables can be defined inline as well.
const ref = getConfirmedBookingForCoachSlotRef({ coachId: ..., bookingDate: ..., timeSlotId: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getConfirmedBookingForCoachSlotRef(dataConnect, getConfirmedBookingForCoachSlotVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetConfirmedBookingsForCoachDate
You can execute the `GetConfirmedBookingsForCoachDate` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getConfirmedBookingsForCoachDate(vars: GetConfirmedBookingsForCoachDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingsForCoachDateData, GetConfirmedBookingsForCoachDateVariables>;

interface GetConfirmedBookingsForCoachDateRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetConfirmedBookingsForCoachDateVariables): QueryRef<GetConfirmedBookingsForCoachDateData, GetConfirmedBookingsForCoachDateVariables>;
}
export const getConfirmedBookingsForCoachDateRef: GetConfirmedBookingsForCoachDateRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getConfirmedBookingsForCoachDate(dc: DataConnect, vars: GetConfirmedBookingsForCoachDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingsForCoachDateData, GetConfirmedBookingsForCoachDateVariables>;

interface GetConfirmedBookingsForCoachDateRef {
  ...
  (dc: DataConnect, vars: GetConfirmedBookingsForCoachDateVariables): QueryRef<GetConfirmedBookingsForCoachDateData, GetConfirmedBookingsForCoachDateVariables>;
}
export const getConfirmedBookingsForCoachDateRef: GetConfirmedBookingsForCoachDateRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getConfirmedBookingsForCoachDateRef:
```typescript
const name = getConfirmedBookingsForCoachDateRef.operationName;
console.log(name);
```

### Variables
The `GetConfirmedBookingsForCoachDate` query requires an argument of type `GetConfirmedBookingsForCoachDateVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetConfirmedBookingsForCoachDateVariables {
  coachId: UUIDString;
  bookingDate: DateString;
}
```
### Return Type
Recall that executing the `GetConfirmedBookingsForCoachDate` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetConfirmedBookingsForCoachDateData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetConfirmedBookingsForCoachDateData {
  bookings: ({
    id: UUIDString;
    timeSlotId: UUIDString;
  } & Booking_Key)[];
}
```
### Using `GetConfirmedBookingsForCoachDate`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getConfirmedBookingsForCoachDate, GetConfirmedBookingsForCoachDateVariables } from '@firebasegen/example-connector';

// The `GetConfirmedBookingsForCoachDate` query requires an argument of type `GetConfirmedBookingsForCoachDateVariables`:
const getConfirmedBookingsForCoachDateVars: GetConfirmedBookingsForCoachDateVariables = {
  coachId: ..., 
  bookingDate: ..., 
};

// Call the `getConfirmedBookingsForCoachDate()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getConfirmedBookingsForCoachDate(getConfirmedBookingsForCoachDateVars);
// Variables can be defined inline as well.
const { data } = await getConfirmedBookingsForCoachDate({ coachId: ..., bookingDate: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getConfirmedBookingsForCoachDate(dataConnect, getConfirmedBookingsForCoachDateVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getConfirmedBookingsForCoachDate(getConfirmedBookingsForCoachDateVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetConfirmedBookingsForCoachDate`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getConfirmedBookingsForCoachDateRef, GetConfirmedBookingsForCoachDateVariables } from '@firebasegen/example-connector';

// The `GetConfirmedBookingsForCoachDate` query requires an argument of type `GetConfirmedBookingsForCoachDateVariables`:
const getConfirmedBookingsForCoachDateVars: GetConfirmedBookingsForCoachDateVariables = {
  coachId: ..., 
  bookingDate: ..., 
};

// Call the `getConfirmedBookingsForCoachDateRef()` function to get a reference to the query.
const ref = getConfirmedBookingsForCoachDateRef(getConfirmedBookingsForCoachDateVars);
// Variables can be defined inline as well.
const ref = getConfirmedBookingsForCoachDateRef({ coachId: ..., bookingDate: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getConfirmedBookingsForCoachDateRef(dataConnect, getConfirmedBookingsForCoachDateVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetConfirmedBookingsByCoach
You can execute the `GetConfirmedBookingsByCoach` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getConfirmedBookingsByCoach(vars: GetConfirmedBookingsByCoachVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingsByCoachData, GetConfirmedBookingsByCoachVariables>;

interface GetConfirmedBookingsByCoachRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetConfirmedBookingsByCoachVariables): QueryRef<GetConfirmedBookingsByCoachData, GetConfirmedBookingsByCoachVariables>;
}
export const getConfirmedBookingsByCoachRef: GetConfirmedBookingsByCoachRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getConfirmedBookingsByCoach(dc: DataConnect, vars: GetConfirmedBookingsByCoachVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingsByCoachData, GetConfirmedBookingsByCoachVariables>;

interface GetConfirmedBookingsByCoachRef {
  ...
  (dc: DataConnect, vars: GetConfirmedBookingsByCoachVariables): QueryRef<GetConfirmedBookingsByCoachData, GetConfirmedBookingsByCoachVariables>;
}
export const getConfirmedBookingsByCoachRef: GetConfirmedBookingsByCoachRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getConfirmedBookingsByCoachRef:
```typescript
const name = getConfirmedBookingsByCoachRef.operationName;
console.log(name);
```

### Variables
The `GetConfirmedBookingsByCoach` query requires an argument of type `GetConfirmedBookingsByCoachVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetConfirmedBookingsByCoachVariables {
  coachId: UUIDString;
}
```
### Return Type
Recall that executing the `GetConfirmedBookingsByCoach` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetConfirmedBookingsByCoachData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetConfirmedBookingsByCoachData {
  bookings: ({
    id: UUIDString;
    bookingDate: DateString;
    bookingType: string;
    status: string;
    bookedAt: TimestampString;
    cancelledAt?: TimestampString | null;
    createdAt: TimestampString;
    court: {
      id: UUIDString;
      name: string;
    } & Court_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
    } & TimeSlot_Key;
    createdBy: {
      id: UUIDString;
      fullName: string;
    } & User_Key;
  } & Booking_Key)[];
}
```
### Using `GetConfirmedBookingsByCoach`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getConfirmedBookingsByCoach, GetConfirmedBookingsByCoachVariables } from '@firebasegen/example-connector';

// The `GetConfirmedBookingsByCoach` query requires an argument of type `GetConfirmedBookingsByCoachVariables`:
const getConfirmedBookingsByCoachVars: GetConfirmedBookingsByCoachVariables = {
  coachId: ..., 
};

// Call the `getConfirmedBookingsByCoach()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getConfirmedBookingsByCoach(getConfirmedBookingsByCoachVars);
// Variables can be defined inline as well.
const { data } = await getConfirmedBookingsByCoach({ coachId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getConfirmedBookingsByCoach(dataConnect, getConfirmedBookingsByCoachVars);

console.log(data.bookings);

// Or, you can use the `Promise` API.
getConfirmedBookingsByCoach(getConfirmedBookingsByCoachVars).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `GetConfirmedBookingsByCoach`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getConfirmedBookingsByCoachRef, GetConfirmedBookingsByCoachVariables } from '@firebasegen/example-connector';

// The `GetConfirmedBookingsByCoach` query requires an argument of type `GetConfirmedBookingsByCoachVariables`:
const getConfirmedBookingsByCoachVars: GetConfirmedBookingsByCoachVariables = {
  coachId: ..., 
};

// Call the `getConfirmedBookingsByCoachRef()` function to get a reference to the query.
const ref = getConfirmedBookingsByCoachRef(getConfirmedBookingsByCoachVars);
// Variables can be defined inline as well.
const ref = getConfirmedBookingsByCoachRef({ coachId: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getConfirmedBookingsByCoachRef(dataConnect, getConfirmedBookingsByCoachVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.bookings);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

## GetCoachAvailabilityNoticeBoard
You can execute the `GetCoachAvailabilityNoticeBoard` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getCoachAvailabilityNoticeBoard(vars: GetCoachAvailabilityNoticeBoardVariables, options?: ExecuteQueryOptions): QueryPromise<GetCoachAvailabilityNoticeBoardData, GetCoachAvailabilityNoticeBoardVariables>;

interface GetCoachAvailabilityNoticeBoardRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetCoachAvailabilityNoticeBoardVariables): QueryRef<GetCoachAvailabilityNoticeBoardData, GetCoachAvailabilityNoticeBoardVariables>;
}
export const getCoachAvailabilityNoticeBoardRef: GetCoachAvailabilityNoticeBoardRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getCoachAvailabilityNoticeBoard(dc: DataConnect, vars: GetCoachAvailabilityNoticeBoardVariables, options?: ExecuteQueryOptions): QueryPromise<GetCoachAvailabilityNoticeBoardData, GetCoachAvailabilityNoticeBoardVariables>;

interface GetCoachAvailabilityNoticeBoardRef {
  ...
  (dc: DataConnect, vars: GetCoachAvailabilityNoticeBoardVariables): QueryRef<GetCoachAvailabilityNoticeBoardData, GetCoachAvailabilityNoticeBoardVariables>;
}
export const getCoachAvailabilityNoticeBoardRef: GetCoachAvailabilityNoticeBoardRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getCoachAvailabilityNoticeBoardRef:
```typescript
const name = getCoachAvailabilityNoticeBoardRef.operationName;
console.log(name);
```

### Variables
The `GetCoachAvailabilityNoticeBoard` query requires an argument of type `GetCoachAvailabilityNoticeBoardVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetCoachAvailabilityNoticeBoardVariables {
  availabilityDate: DateString;
}
```
### Return Type
Recall that executing the `GetCoachAvailabilityNoticeBoard` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetCoachAvailabilityNoticeBoardData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetCoachAvailabilityNoticeBoardData {
  coachAvailabilities: ({
    availabilityDate: DateString;
    createdAt: TimestampString;
    coachId: UUIDString;
    timeSlotId: UUIDString;
    coach: {
      id: UUIDString;
      fullName: string;
      role: string;
      status: string;
    } & User_Key;
    timeSlot: {
      id: UUIDString;
      startTime: string;
      endTime: string;
      displayOrder: number;
      isActive: boolean;
    } & TimeSlot_Key;
  } & CoachAvailability_Key)[];
}
```
### Using `GetCoachAvailabilityNoticeBoard`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getCoachAvailabilityNoticeBoard, GetCoachAvailabilityNoticeBoardVariables } from '@firebasegen/example-connector';

// The `GetCoachAvailabilityNoticeBoard` query requires an argument of type `GetCoachAvailabilityNoticeBoardVariables`:
const getCoachAvailabilityNoticeBoardVars: GetCoachAvailabilityNoticeBoardVariables = {
  availabilityDate: ..., 
};

// Call the `getCoachAvailabilityNoticeBoard()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getCoachAvailabilityNoticeBoard(getCoachAvailabilityNoticeBoardVars);
// Variables can be defined inline as well.
const { data } = await getCoachAvailabilityNoticeBoard({ availabilityDate: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getCoachAvailabilityNoticeBoard(dataConnect, getCoachAvailabilityNoticeBoardVars);

console.log(data.coachAvailabilities);

// Or, you can use the `Promise` API.
getCoachAvailabilityNoticeBoard(getCoachAvailabilityNoticeBoardVars).then((response) => {
  const data = response.data;
  console.log(data.coachAvailabilities);
});
```

### Using `GetCoachAvailabilityNoticeBoard`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getCoachAvailabilityNoticeBoardRef, GetCoachAvailabilityNoticeBoardVariables } from '@firebasegen/example-connector';

// The `GetCoachAvailabilityNoticeBoard` query requires an argument of type `GetCoachAvailabilityNoticeBoardVariables`:
const getCoachAvailabilityNoticeBoardVars: GetCoachAvailabilityNoticeBoardVariables = {
  availabilityDate: ..., 
};

// Call the `getCoachAvailabilityNoticeBoardRef()` function to get a reference to the query.
const ref = getCoachAvailabilityNoticeBoardRef(getCoachAvailabilityNoticeBoardVars);
// Variables can be defined inline as well.
const ref = getCoachAvailabilityNoticeBoardRef({ availabilityDate: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getCoachAvailabilityNoticeBoardRef(dataConnect, getCoachAvailabilityNoticeBoardVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.coachAvailabilities);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.coachAvailabilities);
});
```

# Mutations

There are two ways to execute a Data Connect Mutation using the generated Web SDK:
- Using a Mutation Reference function, which returns a `MutationRef`
  - The `MutationRef` can be used as an argument to `executeMutation()`, which will execute the Mutation and return a `MutationPromise`
- Using an action shortcut function, which returns a `MutationPromise`
  - Calling the action shortcut function will execute the Mutation and return a `MutationPromise`

The following is true for both the action shortcut function and the `MutationRef` function:
- The `MutationPromise` returned will resolve to the result of the Mutation once it has finished executing
- If the Mutation accepts arguments, both the action shortcut function and the `MutationRef` function accept a single argument: an object that contains all the required variables (and the optional variables) for the Mutation
- Both functions can be called with or without passing in a `DataConnect` instance as an argument. If no `DataConnect` argument is passed in, then the generated SDK will call `getDataConnect(connectorConfig)` behind the scenes for you.

Below are examples of how to use the `example` connector's generated functions to execute each mutation. You can also follow the examples from the [Data Connect documentation](https://firebase.google.com/docs/data-connect/web-sdk#using-mutations).

## UpsertUser
You can execute the `UpsertUser` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
upsertUser(vars: UpsertUserVariables): MutationPromise<UpsertUserData, UpsertUserVariables>;

interface UpsertUserRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpsertUserVariables): MutationRef<UpsertUserData, UpsertUserVariables>;
}
export const upsertUserRef: UpsertUserRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
upsertUser(dc: DataConnect, vars: UpsertUserVariables): MutationPromise<UpsertUserData, UpsertUserVariables>;

interface UpsertUserRef {
  ...
  (dc: DataConnect, vars: UpsertUserVariables): MutationRef<UpsertUserData, UpsertUserVariables>;
}
export const upsertUserRef: UpsertUserRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the upsertUserRef:
```typescript
const name = upsertUserRef.operationName;
console.log(name);
```

### Variables
The `UpsertUser` mutation requires an argument of type `UpsertUserVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface UpsertUserVariables {
  firebaseUid: string;
  fullName: string;
  email: string;
  studentNumber?: string | null;
  role?: string | null;
  status?: string | null;
}
```
### Return Type
Recall that executing the `UpsertUser` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `UpsertUserData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface UpsertUserData {
  user_insert: User_Key;
}
```
### Using `UpsertUser`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, upsertUser, UpsertUserVariables } from '@firebasegen/example-connector';

// The `UpsertUser` mutation requires an argument of type `UpsertUserVariables`:
const upsertUserVars: UpsertUserVariables = {
  firebaseUid: ..., 
  fullName: ..., 
  email: ..., 
  studentNumber: ..., // optional
  role: ..., // optional
  status: ..., // optional
};

// Call the `upsertUser()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await upsertUser(upsertUserVars);
// Variables can be defined inline as well.
const { data } = await upsertUser({ firebaseUid: ..., fullName: ..., email: ..., studentNumber: ..., role: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await upsertUser(dataConnect, upsertUserVars);

console.log(data.user_insert);

// Or, you can use the `Promise` API.
upsertUser(upsertUserVars).then((response) => {
  const data = response.data;
  console.log(data.user_insert);
});
```

### Using `UpsertUser`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, upsertUserRef, UpsertUserVariables } from '@firebasegen/example-connector';

// The `UpsertUser` mutation requires an argument of type `UpsertUserVariables`:
const upsertUserVars: UpsertUserVariables = {
  firebaseUid: ..., 
  fullName: ..., 
  email: ..., 
  studentNumber: ..., // optional
  role: ..., // optional
  status: ..., // optional
};

// Call the `upsertUserRef()` function to get a reference to the mutation.
const ref = upsertUserRef(upsertUserVars);
// Variables can be defined inline as well.
const ref = upsertUserRef({ firebaseUid: ..., fullName: ..., email: ..., studentNumber: ..., role: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = upsertUserRef(dataConnect, upsertUserVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.user_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.user_insert);
});
```

## UpdateUserProfile
You can execute the `UpdateUserProfile` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
updateUserProfile(vars: UpdateUserProfileVariables): MutationPromise<UpdateUserProfileData, UpdateUserProfileVariables>;

interface UpdateUserProfileRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpdateUserProfileVariables): MutationRef<UpdateUserProfileData, UpdateUserProfileVariables>;
}
export const updateUserProfileRef: UpdateUserProfileRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
updateUserProfile(dc: DataConnect, vars: UpdateUserProfileVariables): MutationPromise<UpdateUserProfileData, UpdateUserProfileVariables>;

interface UpdateUserProfileRef {
  ...
  (dc: DataConnect, vars: UpdateUserProfileVariables): MutationRef<UpdateUserProfileData, UpdateUserProfileVariables>;
}
export const updateUserProfileRef: UpdateUserProfileRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the updateUserProfileRef:
```typescript
const name = updateUserProfileRef.operationName;
console.log(name);
```

### Variables
The `UpdateUserProfile` mutation requires an argument of type `UpdateUserProfileVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface UpdateUserProfileVariables {
  firebaseUid: string;
  fullName: string;
  studentNumber?: string | null;
}
```
### Return Type
Recall that executing the `UpdateUserProfile` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `UpdateUserProfileData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface UpdateUserProfileData {
  user_updateMany: number;
}
```
### Using `UpdateUserProfile`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, updateUserProfile, UpdateUserProfileVariables } from '@firebasegen/example-connector';

// The `UpdateUserProfile` mutation requires an argument of type `UpdateUserProfileVariables`:
const updateUserProfileVars: UpdateUserProfileVariables = {
  firebaseUid: ..., 
  fullName: ..., 
  studentNumber: ..., // optional
};

// Call the `updateUserProfile()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await updateUserProfile(updateUserProfileVars);
// Variables can be defined inline as well.
const { data } = await updateUserProfile({ firebaseUid: ..., fullName: ..., studentNumber: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await updateUserProfile(dataConnect, updateUserProfileVars);

console.log(data.user_updateMany);

// Or, you can use the `Promise` API.
updateUserProfile(updateUserProfileVars).then((response) => {
  const data = response.data;
  console.log(data.user_updateMany);
});
```

### Using `UpdateUserProfile`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, updateUserProfileRef, UpdateUserProfileVariables } from '@firebasegen/example-connector';

// The `UpdateUserProfile` mutation requires an argument of type `UpdateUserProfileVariables`:
const updateUserProfileVars: UpdateUserProfileVariables = {
  firebaseUid: ..., 
  fullName: ..., 
  studentNumber: ..., // optional
};

// Call the `updateUserProfileRef()` function to get a reference to the mutation.
const ref = updateUserProfileRef(updateUserProfileVars);
// Variables can be defined inline as well.
const ref = updateUserProfileRef({ firebaseUid: ..., fullName: ..., studentNumber: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = updateUserProfileRef(dataConnect, updateUserProfileVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.user_updateMany);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.user_updateMany);
});
```

## UpdateUser
You can execute the `UpdateUser` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
updateUser(vars: UpdateUserVariables): MutationPromise<UpdateUserData, UpdateUserVariables>;

interface UpdateUserRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpdateUserVariables): MutationRef<UpdateUserData, UpdateUserVariables>;
}
export const updateUserRef: UpdateUserRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
updateUser(dc: DataConnect, vars: UpdateUserVariables): MutationPromise<UpdateUserData, UpdateUserVariables>;

interface UpdateUserRef {
  ...
  (dc: DataConnect, vars: UpdateUserVariables): MutationRef<UpdateUserData, UpdateUserVariables>;
}
export const updateUserRef: UpdateUserRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the updateUserRef:
```typescript
const name = updateUserRef.operationName;
console.log(name);
```

### Variables
The `UpdateUser` mutation requires an argument of type `UpdateUserVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface UpdateUserVariables {
  id: UUIDString;
  role?: string | null;
  status?: string | null;
}
```
### Return Type
Recall that executing the `UpdateUser` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `UpdateUserData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface UpdateUserData {
  user_update?: User_Key | null;
}
```
### Using `UpdateUser`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, updateUser, UpdateUserVariables } from '@firebasegen/example-connector';

// The `UpdateUser` mutation requires an argument of type `UpdateUserVariables`:
const updateUserVars: UpdateUserVariables = {
  id: ..., 
  role: ..., // optional
  status: ..., // optional
};

// Call the `updateUser()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await updateUser(updateUserVars);
// Variables can be defined inline as well.
const { data } = await updateUser({ id: ..., role: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await updateUser(dataConnect, updateUserVars);

console.log(data.user_update);

// Or, you can use the `Promise` API.
updateUser(updateUserVars).then((response) => {
  const data = response.data;
  console.log(data.user_update);
});
```

### Using `UpdateUser`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, updateUserRef, UpdateUserVariables } from '@firebasegen/example-connector';

// The `UpdateUser` mutation requires an argument of type `UpdateUserVariables`:
const updateUserVars: UpdateUserVariables = {
  id: ..., 
  role: ..., // optional
  status: ..., // optional
};

// Call the `updateUserRef()` function to get a reference to the mutation.
const ref = updateUserRef(updateUserVars);
// Variables can be defined inline as well.
const ref = updateUserRef({ id: ..., role: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = updateUserRef(dataConnect, updateUserVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.user_update);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.user_update);
});
```

## DeleteUser
You can execute the `DeleteUser` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
deleteUser(vars: DeleteUserVariables): MutationPromise<DeleteUserData, DeleteUserVariables>;

interface DeleteUserRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: DeleteUserVariables): MutationRef<DeleteUserData, DeleteUserVariables>;
}
export const deleteUserRef: DeleteUserRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
deleteUser(dc: DataConnect, vars: DeleteUserVariables): MutationPromise<DeleteUserData, DeleteUserVariables>;

interface DeleteUserRef {
  ...
  (dc: DataConnect, vars: DeleteUserVariables): MutationRef<DeleteUserData, DeleteUserVariables>;
}
export const deleteUserRef: DeleteUserRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the deleteUserRef:
```typescript
const name = deleteUserRef.operationName;
console.log(name);
```

### Variables
The `DeleteUser` mutation requires an argument of type `DeleteUserVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface DeleteUserVariables {
  id: UUIDString;
}
```
### Return Type
Recall that executing the `DeleteUser` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `DeleteUserData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface DeleteUserData {
  user_delete?: User_Key | null;
}
```
### Using `DeleteUser`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, deleteUser, DeleteUserVariables } from '@firebasegen/example-connector';

// The `DeleteUser` mutation requires an argument of type `DeleteUserVariables`:
const deleteUserVars: DeleteUserVariables = {
  id: ..., 
};

// Call the `deleteUser()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await deleteUser(deleteUserVars);
// Variables can be defined inline as well.
const { data } = await deleteUser({ id: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await deleteUser(dataConnect, deleteUserVars);

console.log(data.user_delete);

// Or, you can use the `Promise` API.
deleteUser(deleteUserVars).then((response) => {
  const data = response.data;
  console.log(data.user_delete);
});
```

### Using `DeleteUser`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, deleteUserRef, DeleteUserVariables } from '@firebasegen/example-connector';

// The `DeleteUser` mutation requires an argument of type `DeleteUserVariables`:
const deleteUserVars: DeleteUserVariables = {
  id: ..., 
};

// Call the `deleteUserRef()` function to get a reference to the mutation.
const ref = deleteUserRef(deleteUserVars);
// Variables can be defined inline as well.
const ref = deleteUserRef({ id: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = deleteUserRef(dataConnect, deleteUserVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.user_delete);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.user_delete);
});
```

## CreateCourt
You can execute the `CreateCourt` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
createCourt(vars: CreateCourtVariables): MutationPromise<CreateCourtData, CreateCourtVariables>;

interface CreateCourtRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateCourtVariables): MutationRef<CreateCourtData, CreateCourtVariables>;
}
export const createCourtRef: CreateCourtRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createCourt(dc: DataConnect, vars: CreateCourtVariables): MutationPromise<CreateCourtData, CreateCourtVariables>;

interface CreateCourtRef {
  ...
  (dc: DataConnect, vars: CreateCourtVariables): MutationRef<CreateCourtData, CreateCourtVariables>;
}
export const createCourtRef: CreateCourtRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createCourtRef:
```typescript
const name = createCourtRef.operationName;
console.log(name);
```

### Variables
The `CreateCourt` mutation requires an argument of type `CreateCourtVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateCourtVariables {
  name: string;
  description?: string | null;
  status?: string | null;
}
```
### Return Type
Recall that executing the `CreateCourt` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateCourtData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateCourtData {
  court_insert: Court_Key;
}
```
### Using `CreateCourt`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createCourt, CreateCourtVariables } from '@firebasegen/example-connector';

// The `CreateCourt` mutation requires an argument of type `CreateCourtVariables`:
const createCourtVars: CreateCourtVariables = {
  name: ..., 
  description: ..., // optional
  status: ..., // optional
};

// Call the `createCourt()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createCourt(createCourtVars);
// Variables can be defined inline as well.
const { data } = await createCourt({ name: ..., description: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createCourt(dataConnect, createCourtVars);

console.log(data.court_insert);

// Or, you can use the `Promise` API.
createCourt(createCourtVars).then((response) => {
  const data = response.data;
  console.log(data.court_insert);
});
```

### Using `CreateCourt`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createCourtRef, CreateCourtVariables } from '@firebasegen/example-connector';

// The `CreateCourt` mutation requires an argument of type `CreateCourtVariables`:
const createCourtVars: CreateCourtVariables = {
  name: ..., 
  description: ..., // optional
  status: ..., // optional
};

// Call the `createCourtRef()` function to get a reference to the mutation.
const ref = createCourtRef(createCourtVars);
// Variables can be defined inline as well.
const ref = createCourtRef({ name: ..., description: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createCourtRef(dataConnect, createCourtVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.court_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.court_insert);
});
```

## UpdateCourtStatus
You can execute the `UpdateCourtStatus` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
updateCourtStatus(vars: UpdateCourtStatusVariables): MutationPromise<UpdateCourtStatusData, UpdateCourtStatusVariables>;

interface UpdateCourtStatusRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpdateCourtStatusVariables): MutationRef<UpdateCourtStatusData, UpdateCourtStatusVariables>;
}
export const updateCourtStatusRef: UpdateCourtStatusRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
updateCourtStatus(dc: DataConnect, vars: UpdateCourtStatusVariables): MutationPromise<UpdateCourtStatusData, UpdateCourtStatusVariables>;

interface UpdateCourtStatusRef {
  ...
  (dc: DataConnect, vars: UpdateCourtStatusVariables): MutationRef<UpdateCourtStatusData, UpdateCourtStatusVariables>;
}
export const updateCourtStatusRef: UpdateCourtStatusRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the updateCourtStatusRef:
```typescript
const name = updateCourtStatusRef.operationName;
console.log(name);
```

### Variables
The `UpdateCourtStatus` mutation requires an argument of type `UpdateCourtStatusVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface UpdateCourtStatusVariables {
  id: UUIDString;
  status: string;
}
```
### Return Type
Recall that executing the `UpdateCourtStatus` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `UpdateCourtStatusData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface UpdateCourtStatusData {
  court_update?: Court_Key | null;
}
```
### Using `UpdateCourtStatus`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, updateCourtStatus, UpdateCourtStatusVariables } from '@firebasegen/example-connector';

// The `UpdateCourtStatus` mutation requires an argument of type `UpdateCourtStatusVariables`:
const updateCourtStatusVars: UpdateCourtStatusVariables = {
  id: ..., 
  status: ..., 
};

// Call the `updateCourtStatus()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await updateCourtStatus(updateCourtStatusVars);
// Variables can be defined inline as well.
const { data } = await updateCourtStatus({ id: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await updateCourtStatus(dataConnect, updateCourtStatusVars);

console.log(data.court_update);

// Or, you can use the `Promise` API.
updateCourtStatus(updateCourtStatusVars).then((response) => {
  const data = response.data;
  console.log(data.court_update);
});
```

### Using `UpdateCourtStatus`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, updateCourtStatusRef, UpdateCourtStatusVariables } from '@firebasegen/example-connector';

// The `UpdateCourtStatus` mutation requires an argument of type `UpdateCourtStatusVariables`:
const updateCourtStatusVars: UpdateCourtStatusVariables = {
  id: ..., 
  status: ..., 
};

// Call the `updateCourtStatusRef()` function to get a reference to the mutation.
const ref = updateCourtStatusRef(updateCourtStatusVars);
// Variables can be defined inline as well.
const ref = updateCourtStatusRef({ id: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = updateCourtStatusRef(dataConnect, updateCourtStatusVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.court_update);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.court_update);
});
```

## UpdateCourt
You can execute the `UpdateCourt` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
updateCourt(vars: UpdateCourtVariables): MutationPromise<UpdateCourtData, UpdateCourtVariables>;

interface UpdateCourtRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpdateCourtVariables): MutationRef<UpdateCourtData, UpdateCourtVariables>;
}
export const updateCourtRef: UpdateCourtRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
updateCourt(dc: DataConnect, vars: UpdateCourtVariables): MutationPromise<UpdateCourtData, UpdateCourtVariables>;

interface UpdateCourtRef {
  ...
  (dc: DataConnect, vars: UpdateCourtVariables): MutationRef<UpdateCourtData, UpdateCourtVariables>;
}
export const updateCourtRef: UpdateCourtRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the updateCourtRef:
```typescript
const name = updateCourtRef.operationName;
console.log(name);
```

### Variables
The `UpdateCourt` mutation requires an argument of type `UpdateCourtVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface UpdateCourtVariables {
  id: UUIDString;
  name?: string | null;
  description?: string | null;
  status?: string | null;
}
```
### Return Type
Recall that executing the `UpdateCourt` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `UpdateCourtData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface UpdateCourtData {
  court_update?: Court_Key | null;
}
```
### Using `UpdateCourt`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, updateCourt, UpdateCourtVariables } from '@firebasegen/example-connector';

// The `UpdateCourt` mutation requires an argument of type `UpdateCourtVariables`:
const updateCourtVars: UpdateCourtVariables = {
  id: ..., 
  name: ..., // optional
  description: ..., // optional
  status: ..., // optional
};

// Call the `updateCourt()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await updateCourt(updateCourtVars);
// Variables can be defined inline as well.
const { data } = await updateCourt({ id: ..., name: ..., description: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await updateCourt(dataConnect, updateCourtVars);

console.log(data.court_update);

// Or, you can use the `Promise` API.
updateCourt(updateCourtVars).then((response) => {
  const data = response.data;
  console.log(data.court_update);
});
```

### Using `UpdateCourt`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, updateCourtRef, UpdateCourtVariables } from '@firebasegen/example-connector';

// The `UpdateCourt` mutation requires an argument of type `UpdateCourtVariables`:
const updateCourtVars: UpdateCourtVariables = {
  id: ..., 
  name: ..., // optional
  description: ..., // optional
  status: ..., // optional
};

// Call the `updateCourtRef()` function to get a reference to the mutation.
const ref = updateCourtRef(updateCourtVars);
// Variables can be defined inline as well.
const ref = updateCourtRef({ id: ..., name: ..., description: ..., status: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = updateCourtRef(dataConnect, updateCourtVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.court_update);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.court_update);
});
```

## DeleteCourt
You can execute the `DeleteCourt` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
deleteCourt(vars: DeleteCourtVariables): MutationPromise<DeleteCourtData, DeleteCourtVariables>;

interface DeleteCourtRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: DeleteCourtVariables): MutationRef<DeleteCourtData, DeleteCourtVariables>;
}
export const deleteCourtRef: DeleteCourtRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
deleteCourt(dc: DataConnect, vars: DeleteCourtVariables): MutationPromise<DeleteCourtData, DeleteCourtVariables>;

interface DeleteCourtRef {
  ...
  (dc: DataConnect, vars: DeleteCourtVariables): MutationRef<DeleteCourtData, DeleteCourtVariables>;
}
export const deleteCourtRef: DeleteCourtRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the deleteCourtRef:
```typescript
const name = deleteCourtRef.operationName;
console.log(name);
```

### Variables
The `DeleteCourt` mutation requires an argument of type `DeleteCourtVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface DeleteCourtVariables {
  id: UUIDString;
}
```
### Return Type
Recall that executing the `DeleteCourt` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `DeleteCourtData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface DeleteCourtData {
  court_delete?: Court_Key | null;
}
```
### Using `DeleteCourt`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, deleteCourt, DeleteCourtVariables } from '@firebasegen/example-connector';

// The `DeleteCourt` mutation requires an argument of type `DeleteCourtVariables`:
const deleteCourtVars: DeleteCourtVariables = {
  id: ..., 
};

// Call the `deleteCourt()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await deleteCourt(deleteCourtVars);
// Variables can be defined inline as well.
const { data } = await deleteCourt({ id: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await deleteCourt(dataConnect, deleteCourtVars);

console.log(data.court_delete);

// Or, you can use the `Promise` API.
deleteCourt(deleteCourtVars).then((response) => {
  const data = response.data;
  console.log(data.court_delete);
});
```

### Using `DeleteCourt`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, deleteCourtRef, DeleteCourtVariables } from '@firebasegen/example-connector';

// The `DeleteCourt` mutation requires an argument of type `DeleteCourtVariables`:
const deleteCourtVars: DeleteCourtVariables = {
  id: ..., 
};

// Call the `deleteCourtRef()` function to get a reference to the mutation.
const ref = deleteCourtRef(deleteCourtVars);
// Variables can be defined inline as well.
const ref = deleteCourtRef({ id: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = deleteCourtRef(dataConnect, deleteCourtVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.court_delete);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.court_delete);
});
```

## CreateTimeSlot
You can execute the `CreateTimeSlot` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
createTimeSlot(vars: CreateTimeSlotVariables): MutationPromise<CreateTimeSlotData, CreateTimeSlotVariables>;

interface CreateTimeSlotRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateTimeSlotVariables): MutationRef<CreateTimeSlotData, CreateTimeSlotVariables>;
}
export const createTimeSlotRef: CreateTimeSlotRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createTimeSlot(dc: DataConnect, vars: CreateTimeSlotVariables): MutationPromise<CreateTimeSlotData, CreateTimeSlotVariables>;

interface CreateTimeSlotRef {
  ...
  (dc: DataConnect, vars: CreateTimeSlotVariables): MutationRef<CreateTimeSlotData, CreateTimeSlotVariables>;
}
export const createTimeSlotRef: CreateTimeSlotRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createTimeSlotRef:
```typescript
const name = createTimeSlotRef.operationName;
console.log(name);
```

### Variables
The `CreateTimeSlot` mutation requires an argument of type `CreateTimeSlotVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateTimeSlotVariables {
  startTime: string;
  endTime: string;
  displayOrder: number;
  isActive?: boolean | null;
}
```
### Return Type
Recall that executing the `CreateTimeSlot` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateTimeSlotData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateTimeSlotData {
  timeSlot_insert: TimeSlot_Key;
}
```
### Using `CreateTimeSlot`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createTimeSlot, CreateTimeSlotVariables } from '@firebasegen/example-connector';

// The `CreateTimeSlot` mutation requires an argument of type `CreateTimeSlotVariables`:
const createTimeSlotVars: CreateTimeSlotVariables = {
  startTime: ..., 
  endTime: ..., 
  displayOrder: ..., 
  isActive: ..., // optional
};

// Call the `createTimeSlot()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createTimeSlot(createTimeSlotVars);
// Variables can be defined inline as well.
const { data } = await createTimeSlot({ startTime: ..., endTime: ..., displayOrder: ..., isActive: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createTimeSlot(dataConnect, createTimeSlotVars);

console.log(data.timeSlot_insert);

// Or, you can use the `Promise` API.
createTimeSlot(createTimeSlotVars).then((response) => {
  const data = response.data;
  console.log(data.timeSlot_insert);
});
```

### Using `CreateTimeSlot`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createTimeSlotRef, CreateTimeSlotVariables } from '@firebasegen/example-connector';

// The `CreateTimeSlot` mutation requires an argument of type `CreateTimeSlotVariables`:
const createTimeSlotVars: CreateTimeSlotVariables = {
  startTime: ..., 
  endTime: ..., 
  displayOrder: ..., 
  isActive: ..., // optional
};

// Call the `createTimeSlotRef()` function to get a reference to the mutation.
const ref = createTimeSlotRef(createTimeSlotVars);
// Variables can be defined inline as well.
const ref = createTimeSlotRef({ startTime: ..., endTime: ..., displayOrder: ..., isActive: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createTimeSlotRef(dataConnect, createTimeSlotVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.timeSlot_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.timeSlot_insert);
});
```

## CreateBooking
You can execute the `CreateBooking` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
createBooking(vars: CreateBookingVariables): MutationPromise<CreateBookingData, CreateBookingVariables>;

interface CreateBookingRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateBookingVariables): MutationRef<CreateBookingData, CreateBookingVariables>;
}
export const createBookingRef: CreateBookingRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createBooking(dc: DataConnect, vars: CreateBookingVariables): MutationPromise<CreateBookingData, CreateBookingVariables>;

interface CreateBookingRef {
  ...
  (dc: DataConnect, vars: CreateBookingVariables): MutationRef<CreateBookingData, CreateBookingVariables>;
}
export const createBookingRef: CreateBookingRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createBookingRef:
```typescript
const name = createBookingRef.operationName;
console.log(name);
```

### Variables
The `CreateBooking` mutation requires an argument of type `CreateBookingVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateBookingVariables {
  courtId: UUIDString;
  timeSlotId: UUIDString;
  createdById: UUIDString;
  coachId?: UUIDString | null;
  bookingDate: DateString;
  bookingType: string;
  bookedAt: TimestampString;
}
```
### Return Type
Recall that executing the `CreateBooking` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateBookingData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateBookingData {
  booking_insert: Booking_Key;
}
```
### Using `CreateBooking`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createBooking, CreateBookingVariables } from '@firebasegen/example-connector';

// The `CreateBooking` mutation requires an argument of type `CreateBookingVariables`:
const createBookingVars: CreateBookingVariables = {
  courtId: ..., 
  timeSlotId: ..., 
  createdById: ..., 
  coachId: ..., // optional
  bookingDate: ..., 
  bookingType: ..., 
  bookedAt: ..., 
};

// Call the `createBooking()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createBooking(createBookingVars);
// Variables can be defined inline as well.
const { data } = await createBooking({ courtId: ..., timeSlotId: ..., createdById: ..., coachId: ..., bookingDate: ..., bookingType: ..., bookedAt: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createBooking(dataConnect, createBookingVars);

console.log(data.booking_insert);

// Or, you can use the `Promise` API.
createBooking(createBookingVars).then((response) => {
  const data = response.data;
  console.log(data.booking_insert);
});
```

### Using `CreateBooking`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createBookingRef, CreateBookingVariables } from '@firebasegen/example-connector';

// The `CreateBooking` mutation requires an argument of type `CreateBookingVariables`:
const createBookingVars: CreateBookingVariables = {
  courtId: ..., 
  timeSlotId: ..., 
  createdById: ..., 
  coachId: ..., // optional
  bookingDate: ..., 
  bookingType: ..., 
  bookedAt: ..., 
};

// Call the `createBookingRef()` function to get a reference to the mutation.
const ref = createBookingRef(createBookingVars);
// Variables can be defined inline as well.
const ref = createBookingRef({ courtId: ..., timeSlotId: ..., createdById: ..., coachId: ..., bookingDate: ..., bookingType: ..., bookedAt: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createBookingRef(dataConnect, createBookingVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.booking_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.booking_insert);
});
```

## CancelBooking
You can execute the `CancelBooking` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
cancelBooking(vars: CancelBookingVariables): MutationPromise<CancelBookingData, CancelBookingVariables>;

interface CancelBookingRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CancelBookingVariables): MutationRef<CancelBookingData, CancelBookingVariables>;
}
export const cancelBookingRef: CancelBookingRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
cancelBooking(dc: DataConnect, vars: CancelBookingVariables): MutationPromise<CancelBookingData, CancelBookingVariables>;

interface CancelBookingRef {
  ...
  (dc: DataConnect, vars: CancelBookingVariables): MutationRef<CancelBookingData, CancelBookingVariables>;
}
export const cancelBookingRef: CancelBookingRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the cancelBookingRef:
```typescript
const name = cancelBookingRef.operationName;
console.log(name);
```

### Variables
The `CancelBooking` mutation requires an argument of type `CancelBookingVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CancelBookingVariables {
  id: UUIDString;
  cancelledAt: TimestampString;
}
```
### Return Type
Recall that executing the `CancelBooking` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CancelBookingData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CancelBookingData {
  booking_update?: Booking_Key | null;
}
```
### Using `CancelBooking`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, cancelBooking, CancelBookingVariables } from '@firebasegen/example-connector';

// The `CancelBooking` mutation requires an argument of type `CancelBookingVariables`:
const cancelBookingVars: CancelBookingVariables = {
  id: ..., 
  cancelledAt: ..., 
};

// Call the `cancelBooking()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await cancelBooking(cancelBookingVars);
// Variables can be defined inline as well.
const { data } = await cancelBooking({ id: ..., cancelledAt: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await cancelBooking(dataConnect, cancelBookingVars);

console.log(data.booking_update);

// Or, you can use the `Promise` API.
cancelBooking(cancelBookingVars).then((response) => {
  const data = response.data;
  console.log(data.booking_update);
});
```

### Using `CancelBooking`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, cancelBookingRef, CancelBookingVariables } from '@firebasegen/example-connector';

// The `CancelBooking` mutation requires an argument of type `CancelBookingVariables`:
const cancelBookingVars: CancelBookingVariables = {
  id: ..., 
  cancelledAt: ..., 
};

// Call the `cancelBookingRef()` function to get a reference to the mutation.
const ref = cancelBookingRef(cancelBookingVars);
// Variables can be defined inline as well.
const ref = cancelBookingRef({ id: ..., cancelledAt: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = cancelBookingRef(dataConnect, cancelBookingVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.booking_update);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.booking_update);
});
```

## AddBookingPlayer
You can execute the `AddBookingPlayer` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
addBookingPlayer(vars: AddBookingPlayerVariables): MutationPromise<AddBookingPlayerData, AddBookingPlayerVariables>;

interface AddBookingPlayerRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: AddBookingPlayerVariables): MutationRef<AddBookingPlayerData, AddBookingPlayerVariables>;
}
export const addBookingPlayerRef: AddBookingPlayerRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
addBookingPlayer(dc: DataConnect, vars: AddBookingPlayerVariables): MutationPromise<AddBookingPlayerData, AddBookingPlayerVariables>;

interface AddBookingPlayerRef {
  ...
  (dc: DataConnect, vars: AddBookingPlayerVariables): MutationRef<AddBookingPlayerData, AddBookingPlayerVariables>;
}
export const addBookingPlayerRef: AddBookingPlayerRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the addBookingPlayerRef:
```typescript
const name = addBookingPlayerRef.operationName;
console.log(name);
```

### Variables
The `AddBookingPlayer` mutation requires an argument of type `AddBookingPlayerVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface AddBookingPlayerVariables {
  bookingId: UUIDString;
  userId: UUIDString;
  playerPosition: number;
  joinedAt: TimestampString;
}
```
### Return Type
Recall that executing the `AddBookingPlayer` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `AddBookingPlayerData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface AddBookingPlayerData {
  bookingPlayer_insert: BookingPlayer_Key;
}
```
### Using `AddBookingPlayer`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, addBookingPlayer, AddBookingPlayerVariables } from '@firebasegen/example-connector';

// The `AddBookingPlayer` mutation requires an argument of type `AddBookingPlayerVariables`:
const addBookingPlayerVars: AddBookingPlayerVariables = {
  bookingId: ..., 
  userId: ..., 
  playerPosition: ..., 
  joinedAt: ..., 
};

// Call the `addBookingPlayer()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await addBookingPlayer(addBookingPlayerVars);
// Variables can be defined inline as well.
const { data } = await addBookingPlayer({ bookingId: ..., userId: ..., playerPosition: ..., joinedAt: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await addBookingPlayer(dataConnect, addBookingPlayerVars);

console.log(data.bookingPlayer_insert);

// Or, you can use the `Promise` API.
addBookingPlayer(addBookingPlayerVars).then((response) => {
  const data = response.data;
  console.log(data.bookingPlayer_insert);
});
```

### Using `AddBookingPlayer`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, addBookingPlayerRef, AddBookingPlayerVariables } from '@firebasegen/example-connector';

// The `AddBookingPlayer` mutation requires an argument of type `AddBookingPlayerVariables`:
const addBookingPlayerVars: AddBookingPlayerVariables = {
  bookingId: ..., 
  userId: ..., 
  playerPosition: ..., 
  joinedAt: ..., 
};

// Call the `addBookingPlayerRef()` function to get a reference to the mutation.
const ref = addBookingPlayerRef(addBookingPlayerVars);
// Variables can be defined inline as well.
const ref = addBookingPlayerRef({ bookingId: ..., userId: ..., playerPosition: ..., joinedAt: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = addBookingPlayerRef(dataConnect, addBookingPlayerVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.bookingPlayer_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.bookingPlayer_insert);
});
```

## LeaveBooking
You can execute the `LeaveBooking` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
leaveBooking(vars: LeaveBookingVariables): MutationPromise<LeaveBookingData, LeaveBookingVariables>;

interface LeaveBookingRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: LeaveBookingVariables): MutationRef<LeaveBookingData, LeaveBookingVariables>;
}
export const leaveBookingRef: LeaveBookingRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
leaveBooking(dc: DataConnect, vars: LeaveBookingVariables): MutationPromise<LeaveBookingData, LeaveBookingVariables>;

interface LeaveBookingRef {
  ...
  (dc: DataConnect, vars: LeaveBookingVariables): MutationRef<LeaveBookingData, LeaveBookingVariables>;
}
export const leaveBookingRef: LeaveBookingRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the leaveBookingRef:
```typescript
const name = leaveBookingRef.operationName;
console.log(name);
```

### Variables
The `LeaveBooking` mutation requires an argument of type `LeaveBookingVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface LeaveBookingVariables {
  bookingId: UUIDString;
  userId: UUIDString;
}
```
### Return Type
Recall that executing the `LeaveBooking` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `LeaveBookingData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface LeaveBookingData {
  bookingPlayer_deleteMany: number;
}
```
### Using `LeaveBooking`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, leaveBooking, LeaveBookingVariables } from '@firebasegen/example-connector';

// The `LeaveBooking` mutation requires an argument of type `LeaveBookingVariables`:
const leaveBookingVars: LeaveBookingVariables = {
  bookingId: ..., 
  userId: ..., 
};

// Call the `leaveBooking()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await leaveBooking(leaveBookingVars);
// Variables can be defined inline as well.
const { data } = await leaveBooking({ bookingId: ..., userId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await leaveBooking(dataConnect, leaveBookingVars);

console.log(data.bookingPlayer_deleteMany);

// Or, you can use the `Promise` API.
leaveBooking(leaveBookingVars).then((response) => {
  const data = response.data;
  console.log(data.bookingPlayer_deleteMany);
});
```

### Using `LeaveBooking`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, leaveBookingRef, LeaveBookingVariables } from '@firebasegen/example-connector';

// The `LeaveBooking` mutation requires an argument of type `LeaveBookingVariables`:
const leaveBookingVars: LeaveBookingVariables = {
  bookingId: ..., 
  userId: ..., 
};

// Call the `leaveBookingRef()` function to get a reference to the mutation.
const ref = leaveBookingRef(leaveBookingVars);
// Variables can be defined inline as well.
const ref = leaveBookingRef({ bookingId: ..., userId: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = leaveBookingRef(dataConnect, leaveBookingVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.bookingPlayer_deleteMany);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.bookingPlayer_deleteMany);
});
```

## CreateCoachAvailability
You can execute the `CreateCoachAvailability` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
createCoachAvailability(vars: CreateCoachAvailabilityVariables): MutationPromise<CreateCoachAvailabilityData, CreateCoachAvailabilityVariables>;

interface CreateCoachAvailabilityRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateCoachAvailabilityVariables): MutationRef<CreateCoachAvailabilityData, CreateCoachAvailabilityVariables>;
}
export const createCoachAvailabilityRef: CreateCoachAvailabilityRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createCoachAvailability(dc: DataConnect, vars: CreateCoachAvailabilityVariables): MutationPromise<CreateCoachAvailabilityData, CreateCoachAvailabilityVariables>;

interface CreateCoachAvailabilityRef {
  ...
  (dc: DataConnect, vars: CreateCoachAvailabilityVariables): MutationRef<CreateCoachAvailabilityData, CreateCoachAvailabilityVariables>;
}
export const createCoachAvailabilityRef: CreateCoachAvailabilityRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createCoachAvailabilityRef:
```typescript
const name = createCoachAvailabilityRef.operationName;
console.log(name);
```

### Variables
The `CreateCoachAvailability` mutation requires an argument of type `CreateCoachAvailabilityVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateCoachAvailabilityVariables {
  coachId: UUIDString;
  timeSlotId: UUIDString;
  availabilityDate: DateString;
}
```
### Return Type
Recall that executing the `CreateCoachAvailability` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateCoachAvailabilityData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateCoachAvailabilityData {
  coachAvailability_insert: CoachAvailability_Key;
}
```
### Using `CreateCoachAvailability`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createCoachAvailability, CreateCoachAvailabilityVariables } from '@firebasegen/example-connector';

// The `CreateCoachAvailability` mutation requires an argument of type `CreateCoachAvailabilityVariables`:
const createCoachAvailabilityVars: CreateCoachAvailabilityVariables = {
  coachId: ..., 
  timeSlotId: ..., 
  availabilityDate: ..., 
};

// Call the `createCoachAvailability()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createCoachAvailability(createCoachAvailabilityVars);
// Variables can be defined inline as well.
const { data } = await createCoachAvailability({ coachId: ..., timeSlotId: ..., availabilityDate: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createCoachAvailability(dataConnect, createCoachAvailabilityVars);

console.log(data.coachAvailability_insert);

// Or, you can use the `Promise` API.
createCoachAvailability(createCoachAvailabilityVars).then((response) => {
  const data = response.data;
  console.log(data.coachAvailability_insert);
});
```

### Using `CreateCoachAvailability`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createCoachAvailabilityRef, CreateCoachAvailabilityVariables } from '@firebasegen/example-connector';

// The `CreateCoachAvailability` mutation requires an argument of type `CreateCoachAvailabilityVariables`:
const createCoachAvailabilityVars: CreateCoachAvailabilityVariables = {
  coachId: ..., 
  timeSlotId: ..., 
  availabilityDate: ..., 
};

// Call the `createCoachAvailabilityRef()` function to get a reference to the mutation.
const ref = createCoachAvailabilityRef(createCoachAvailabilityVars);
// Variables can be defined inline as well.
const ref = createCoachAvailabilityRef({ coachId: ..., timeSlotId: ..., availabilityDate: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createCoachAvailabilityRef(dataConnect, createCoachAvailabilityVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.coachAvailability_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.coachAvailability_insert);
});
```

## DeleteCoachAvailabilityForSlot
You can execute the `DeleteCoachAvailabilityForSlot` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
deleteCoachAvailabilityForSlot(vars: DeleteCoachAvailabilityForSlotVariables): MutationPromise<DeleteCoachAvailabilityForSlotData, DeleteCoachAvailabilityForSlotVariables>;

interface DeleteCoachAvailabilityForSlotRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: DeleteCoachAvailabilityForSlotVariables): MutationRef<DeleteCoachAvailabilityForSlotData, DeleteCoachAvailabilityForSlotVariables>;
}
export const deleteCoachAvailabilityForSlotRef: DeleteCoachAvailabilityForSlotRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
deleteCoachAvailabilityForSlot(dc: DataConnect, vars: DeleteCoachAvailabilityForSlotVariables): MutationPromise<DeleteCoachAvailabilityForSlotData, DeleteCoachAvailabilityForSlotVariables>;

interface DeleteCoachAvailabilityForSlotRef {
  ...
  (dc: DataConnect, vars: DeleteCoachAvailabilityForSlotVariables): MutationRef<DeleteCoachAvailabilityForSlotData, DeleteCoachAvailabilityForSlotVariables>;
}
export const deleteCoachAvailabilityForSlotRef: DeleteCoachAvailabilityForSlotRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the deleteCoachAvailabilityForSlotRef:
```typescript
const name = deleteCoachAvailabilityForSlotRef.operationName;
console.log(name);
```

### Variables
The `DeleteCoachAvailabilityForSlot` mutation requires an argument of type `DeleteCoachAvailabilityForSlotVariables`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface DeleteCoachAvailabilityForSlotVariables {
  coachId: UUIDString;
  timeSlotId: UUIDString;
  availabilityDate: DateString;
}
```
### Return Type
Recall that executing the `DeleteCoachAvailabilityForSlot` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `DeleteCoachAvailabilityForSlotData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface DeleteCoachAvailabilityForSlotData {
  coachAvailability_delete?: CoachAvailability_Key | null;
}
```
### Using `DeleteCoachAvailabilityForSlot`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, deleteCoachAvailabilityForSlot, DeleteCoachAvailabilityForSlotVariables } from '@firebasegen/example-connector';

// The `DeleteCoachAvailabilityForSlot` mutation requires an argument of type `DeleteCoachAvailabilityForSlotVariables`:
const deleteCoachAvailabilityForSlotVars: DeleteCoachAvailabilityForSlotVariables = {
  coachId: ..., 
  timeSlotId: ..., 
  availabilityDate: ..., 
};

// Call the `deleteCoachAvailabilityForSlot()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await deleteCoachAvailabilityForSlot(deleteCoachAvailabilityForSlotVars);
// Variables can be defined inline as well.
const { data } = await deleteCoachAvailabilityForSlot({ coachId: ..., timeSlotId: ..., availabilityDate: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await deleteCoachAvailabilityForSlot(dataConnect, deleteCoachAvailabilityForSlotVars);

console.log(data.coachAvailability_delete);

// Or, you can use the `Promise` API.
deleteCoachAvailabilityForSlot(deleteCoachAvailabilityForSlotVars).then((response) => {
  const data = response.data;
  console.log(data.coachAvailability_delete);
});
```

### Using `DeleteCoachAvailabilityForSlot`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, deleteCoachAvailabilityForSlotRef, DeleteCoachAvailabilityForSlotVariables } from '@firebasegen/example-connector';

// The `DeleteCoachAvailabilityForSlot` mutation requires an argument of type `DeleteCoachAvailabilityForSlotVariables`:
const deleteCoachAvailabilityForSlotVars: DeleteCoachAvailabilityForSlotVariables = {
  coachId: ..., 
  timeSlotId: ..., 
  availabilityDate: ..., 
};

// Call the `deleteCoachAvailabilityForSlotRef()` function to get a reference to the mutation.
const ref = deleteCoachAvailabilityForSlotRef(deleteCoachAvailabilityForSlotVars);
// Variables can be defined inline as well.
const ref = deleteCoachAvailabilityForSlotRef({ coachId: ..., timeSlotId: ..., availabilityDate: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = deleteCoachAvailabilityForSlotRef(dataConnect, deleteCoachAvailabilityForSlotVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.coachAvailability_delete);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.coachAvailability_delete);
});
```

