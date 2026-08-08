import { ConnectorConfig, DataConnect, QueryRef, QueryPromise, ExecuteQueryOptions, MutationRef, MutationPromise } from 'firebase/data-connect';

export const connectorConfig: ConnectorConfig;

export type TimestampString = string;
export type UUIDString = string;
export type Int64String = string;
export type DateString = string;




export interface Booking_Key {
  id: UUIDString;
  __typename?: 'Booking_Key';
}

export interface Court_Key {
  id: UUIDString;
  __typename?: 'Court_Key';
}

export interface CreateBookingData {
  booking_insert: Booking_Key;
}

export interface CreateBookingVariables {
  courtId: UUIDString;
  startTime: TimestampString;
  endTime: TimestampString;
}

export interface CreateCourtData {
  court_insert: Court_Key;
}

export interface CreateCourtVariables {
  name: string;
}

export interface GetCourtData {
  court?: {
    id: UUIDString;
    name: string;
    status: string;
    createdAt: TimestampString;
  } & Court_Key;
}

export interface GetCourtVariables {
  id: UUIDString;
}

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

export interface ListCourtsData {
  courts: ({
    id: UUIDString;
    name: string;
    status: string;
    createdAt: TimestampString;
  } & Court_Key)[];
}

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

export interface UpsertUserData {
  user_upsert: User_Key;
}

export interface UpsertUserVariables {
  studentNumber: string;
  name: string;
  email: string;
}

export interface User_Key {
  id: string;
  __typename?: 'User_Key';
}

interface UpsertUserRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpsertUserVariables): MutationRef<UpsertUserData, UpsertUserVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: UpsertUserVariables): MutationRef<UpsertUserData, UpsertUserVariables>;
  operationName: string;
}
export const upsertUserRef: UpsertUserRef;

export function upsertUser(vars: UpsertUserVariables): MutationPromise<UpsertUserData, UpsertUserVariables>;
export function upsertUser(dc: DataConnect, vars: UpsertUserVariables): MutationPromise<UpsertUserData, UpsertUserVariables>;

interface CreateCourtRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateCourtVariables): MutationRef<CreateCourtData, CreateCourtVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateCourtVariables): MutationRef<CreateCourtData, CreateCourtVariables>;
  operationName: string;
}
export const createCourtRef: CreateCourtRef;

export function createCourt(vars: CreateCourtVariables): MutationPromise<CreateCourtData, CreateCourtVariables>;
export function createCourt(dc: DataConnect, vars: CreateCourtVariables): MutationPromise<CreateCourtData, CreateCourtVariables>;

interface CreateBookingRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateBookingVariables): MutationRef<CreateBookingData, CreateBookingVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateBookingVariables): MutationRef<CreateBookingData, CreateBookingVariables>;
  operationName: string;
}
export const createBookingRef: CreateBookingRef;

export function createBooking(vars: CreateBookingVariables): MutationPromise<CreateBookingData, CreateBookingVariables>;
export function createBooking(dc: DataConnect, vars: CreateBookingVariables): MutationPromise<CreateBookingData, CreateBookingVariables>;

interface GetCurrentUserRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<GetCurrentUserData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<GetCurrentUserData, undefined>;
  operationName: string;
}
export const getCurrentUserRef: GetCurrentUserRef;

export function getCurrentUser(options?: ExecuteQueryOptions): QueryPromise<GetCurrentUserData, undefined>;
export function getCurrentUser(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<GetCurrentUserData, undefined>;

interface ListCourtsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListCourtsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListCourtsData, undefined>;
  operationName: string;
}
export const listCourtsRef: ListCourtsRef;

export function listCourts(options?: ExecuteQueryOptions): QueryPromise<ListCourtsData, undefined>;
export function listCourts(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListCourtsData, undefined>;

interface GetCourtRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetCourtVariables): QueryRef<GetCourtData, GetCourtVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetCourtVariables): QueryRef<GetCourtData, GetCourtVariables>;
  operationName: string;
}
export const getCourtRef: GetCourtRef;

export function getCourt(vars: GetCourtVariables, options?: ExecuteQueryOptions): QueryPromise<GetCourtData, GetCourtVariables>;
export function getCourt(dc: DataConnect, vars: GetCourtVariables, options?: ExecuteQueryOptions): QueryPromise<GetCourtData, GetCourtVariables>;

interface MyBookingsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<MyBookingsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<MyBookingsData, undefined>;
  operationName: string;
}
export const myBookingsRef: MyBookingsRef;

export function myBookings(options?: ExecuteQueryOptions): QueryPromise<MyBookingsData, undefined>;
export function myBookings(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<MyBookingsData, undefined>;

