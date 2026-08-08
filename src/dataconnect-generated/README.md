# Generated TypeScript README
This README will guide you through the process of using the generated JavaScript SDK package for the connector `example`. It will also provide examples on how to use your generated SDK to call your Data Connect queries and mutations.

***NOTE:** This README is generated alongside the generated SDK. If you make changes to this file, they will be overwritten when the SDK is regenerated.*

# Table of Contents
- [**Overview**](#generated-javascript-readme)
- [**Accessing the connector**](#accessing-the-connector)
  - [*Connecting to the local Emulator*](#connecting-to-the-local-emulator)
- [**Queries**](#queries)
  - [*GetCurrentUser*](#getcurrentuser)
  - [*ListCourts*](#listcourts)
  - [*GetCourt*](#getcourt)
  - [*MyBookings*](#mybookings)
- [**Mutations**](#mutations)
  - [*UpsertUser*](#upsertuser)
  - [*CreateCourt*](#createcourt)
  - [*CreateBooking*](#createbooking)

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

## GetCurrentUser
You can execute the `GetCurrentUser` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
getCurrentUser(options?: ExecuteQueryOptions): QueryPromise<GetCurrentUserData, undefined>;

interface GetCurrentUserRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<GetCurrentUserData, undefined>;
}
export const getCurrentUserRef: GetCurrentUserRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getCurrentUser(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<GetCurrentUserData, undefined>;

interface GetCurrentUserRef {
  ...
  (dc: DataConnect): QueryRef<GetCurrentUserData, undefined>;
}
export const getCurrentUserRef: GetCurrentUserRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getCurrentUserRef:
```typescript
const name = getCurrentUserRef.operationName;
console.log(name);
```

### Variables
The `GetCurrentUser` query has no variables.
### Return Type
Recall that executing the `GetCurrentUser` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetCurrentUserData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetCurrentUserData {
  user?: {
    id: string;
    studentNumber: string;
    name: string;
    email: string;
    role: string;
    status: string;
    createdAt: TimestampString;
  } & User_Key;
}
```
### Using `GetCurrentUser`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getCurrentUser } from '@firebasegen/example-connector';


// Call the `getCurrentUser()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getCurrentUser();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getCurrentUser(dataConnect);

console.log(data.user);

// Or, you can use the `Promise` API.
getCurrentUser().then((response) => {
  const data = response.data;
  console.log(data.user);
});
```

### Using `GetCurrentUser`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getCurrentUserRef } from '@firebasegen/example-connector';


// Call the `getCurrentUserRef()` function to get a reference to the query.
const ref = getCurrentUserRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getCurrentUserRef(dataConnect);

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

## ListCourts
You can execute the `ListCourts` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
listCourts(options?: ExecuteQueryOptions): QueryPromise<ListCourtsData, undefined>;

interface ListCourtsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListCourtsData, undefined>;
}
export const listCourtsRef: ListCourtsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listCourts(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListCourtsData, undefined>;

interface ListCourtsRef {
  ...
  (dc: DataConnect): QueryRef<ListCourtsData, undefined>;
}
export const listCourtsRef: ListCourtsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listCourtsRef:
```typescript
const name = listCourtsRef.operationName;
console.log(name);
```

### Variables
The `ListCourts` query has no variables.
### Return Type
Recall that executing the `ListCourts` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListCourtsData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListCourtsData {
  courts: ({
    id: UUIDString;
    name: string;
    status: string;
    createdAt: TimestampString;
  } & Court_Key)[];
}
```
### Using `ListCourts`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listCourts } from '@firebasegen/example-connector';


// Call the `listCourts()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listCourts();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listCourts(dataConnect);

console.log(data.courts);

// Or, you can use the `Promise` API.
listCourts().then((response) => {
  const data = response.data;
  console.log(data.courts);
});
```

### Using `ListCourts`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listCourtsRef } from '@firebasegen/example-connector';


// Call the `listCourtsRef()` function to get a reference to the query.
const ref = listCourtsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listCourtsRef(dataConnect);

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
    status: string;
    createdAt: TimestampString;
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

## MyBookings
You can execute the `MyBookings` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect-generated/index.d.ts](./index.d.ts):
```typescript
myBookings(options?: ExecuteQueryOptions): QueryPromise<MyBookingsData, undefined>;

interface MyBookingsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<MyBookingsData, undefined>;
}
export const myBookingsRef: MyBookingsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
myBookings(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<MyBookingsData, undefined>;

interface MyBookingsRef {
  ...
  (dc: DataConnect): QueryRef<MyBookingsData, undefined>;
}
export const myBookingsRef: MyBookingsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the myBookingsRef:
```typescript
const name = myBookingsRef.operationName;
console.log(name);
```

### Variables
The `MyBookings` query has no variables.
### Return Type
Recall that executing the `MyBookings` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `MyBookingsData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface MyBookingsData {
  bookings: ({
    id: UUIDString;
    startTime: TimestampString;
    endTime: TimestampString;
    status: string;
    createdAt: TimestampString;
    court: {
      id: UUIDString;
      name: string;
      status: string;
    } & Court_Key;
  } & Booking_Key)[];
}
```
### Using `MyBookings`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, myBookings } from '@firebasegen/example-connector';


// Call the `myBookings()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await myBookings();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await myBookings(dataConnect);

console.log(data.bookings);

// Or, you can use the `Promise` API.
myBookings().then((response) => {
  const data = response.data;
  console.log(data.bookings);
});
```

### Using `MyBookings`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, myBookingsRef } from '@firebasegen/example-connector';


// Call the `myBookingsRef()` function to get a reference to the query.
const ref = myBookingsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = myBookingsRef(dataConnect);

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
  studentNumber: string;
  name: string;
  email: string;
}
```
### Return Type
Recall that executing the `UpsertUser` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `UpsertUserData`, which is defined in [dataconnect-generated/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface UpsertUserData {
  user_upsert: User_Key;
}
```
### Using `UpsertUser`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, upsertUser, UpsertUserVariables } from '@firebasegen/example-connector';

// The `UpsertUser` mutation requires an argument of type `UpsertUserVariables`:
const upsertUserVars: UpsertUserVariables = {
  studentNumber: ..., 
  name: ..., 
  email: ..., 
};

// Call the `upsertUser()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await upsertUser(upsertUserVars);
// Variables can be defined inline as well.
const { data } = await upsertUser({ studentNumber: ..., name: ..., email: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await upsertUser(dataConnect, upsertUserVars);

console.log(data.user_upsert);

// Or, you can use the `Promise` API.
upsertUser(upsertUserVars).then((response) => {
  const data = response.data;
  console.log(data.user_upsert);
});
```

### Using `UpsertUser`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, upsertUserRef, UpsertUserVariables } from '@firebasegen/example-connector';

// The `UpsertUser` mutation requires an argument of type `UpsertUserVariables`:
const upsertUserVars: UpsertUserVariables = {
  studentNumber: ..., 
  name: ..., 
  email: ..., 
};

// Call the `upsertUserRef()` function to get a reference to the mutation.
const ref = upsertUserRef(upsertUserVars);
// Variables can be defined inline as well.
const ref = upsertUserRef({ studentNumber: ..., name: ..., email: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = upsertUserRef(dataConnect, upsertUserVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.user_upsert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.user_upsert);
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
};

// Call the `createCourt()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createCourt(createCourtVars);
// Variables can be defined inline as well.
const { data } = await createCourt({ name: ..., });

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
};

// Call the `createCourtRef()` function to get a reference to the mutation.
const ref = createCourtRef(createCourtVars);
// Variables can be defined inline as well.
const ref = createCourtRef({ name: ..., });

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
  startTime: TimestampString;
  endTime: TimestampString;
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
  startTime: ..., 
  endTime: ..., 
};

// Call the `createBooking()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createBooking(createBookingVars);
// Variables can be defined inline as well.
const { data } = await createBooking({ courtId: ..., startTime: ..., endTime: ..., });

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
  startTime: ..., 
  endTime: ..., 
};

// Call the `createBookingRef()` function to get a reference to the mutation.
const ref = createBookingRef(createBookingVars);
// Variables can be defined inline as well.
const ref = createBookingRef({ courtId: ..., startTime: ..., endTime: ..., });

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

