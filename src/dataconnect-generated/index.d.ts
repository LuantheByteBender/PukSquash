import { ConnectorConfig, DataConnect, QueryRef, QueryPromise, ExecuteQueryOptions, MutationRef, MutationPromise } from 'firebase/data-connect';

export const connectorConfig: ConnectorConfig;

export type TimestampString = string;
export type UUIDString = string;
export type Int64String = string;
export type DateString = string;




export interface BookingPlayer_Key {
  id: UUIDString;
  __typename?: 'BookingPlayer_Key';
}

export interface Booking_Key {
  id: UUIDString;
  __typename?: 'Booking_Key';
}

export interface CoachAvailability_Key {
  id: UUIDString;
  __typename?: 'CoachAvailability_Key';
}

export interface Court_Key {
  id: UUIDString;
  __typename?: 'Court_Key';
}

export interface CreateBookingData {
  booking_insert: Booking_Key;
  bookingPlayer_insert: BookingPlayer_Key;
}

export interface CreateBookingVariables {
  courtId: UUIDString;
  timeSlotId: UUIDString;
  createdByUserId: UUIDString;
  coachId?: UUIDString | null;
  bookingDate: DateString;
  bookingType: string;
}

export interface CreateCourtData {
  court_insert: Court_Key;
}

export interface CreateCourtVariables {
  name: string;
  description?: string | null;
}

export interface CreateTimeSlotData {
  timeSlot_insert: TimeSlot_Key;
}

export interface CreateTimeSlotVariables {
  startTime: string;
  endTime: string;
  displayOrder: number;
}

export interface CreateUserData {
  user_insert: User_Key;
}

export interface CreateUserVariables {
  firebaseUid: string;
  studentNumber?: string | null;
  fullName: string;
  email: string;
}

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

export interface GetCourtVariables {
  id: UUIDString;
}

export interface GetCurrentUserData {
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

export interface ListCourtsData {
  courts: ({
    id: UUIDString;
    name: string;
    description?: string | null;
    status: string;
    createdAt: TimestampString;
    updatedAt: TimestampString;
  } & Court_Key)[];
}

export interface ListTimeSlotsData {
  timeSlots: ({
    id: UUIDString;
    startTime: string;
    endTime: string;
    displayOrder: number;
    isActive: boolean;
  } & TimeSlot_Key)[];
}

export interface MyBookingsData {
  users: ({
    id: UUIDString;
    bookings_on_createdByUser: ({
      id: UUIDString;
      bookingDate: DateString;
      bookingType: string;
      status: string;
      bookedAt: TimestampString;
      cancelledAt?: TimestampString | null;
      createdAt: TimestampString;
      updatedAt: TimestampString;
      court: {
        id: UUIDString;
        name: string;
        status: string;
      } & Court_Key;
      timeSlot: {
        id: UUIDString;
        startTime: string;
        endTime: string;
        displayOrder: number;
      } & TimeSlot_Key;
      coach?: {
        id: UUIDString;
        fullName: string;
      } & User_Key;
    } & Booking_Key)[];
  } & User_Key)[];
}

export interface TimeSlot_Key {
  id: UUIDString;
  __typename?: 'TimeSlot_Key';
}

export interface User_Key {
  id: UUIDString;
  __typename?: 'User_Key';
}

interface CreateUserRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateUserVariables): MutationRef<CreateUserData, CreateUserVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateUserVariables): MutationRef<CreateUserData, CreateUserVariables>;
  operationName: string;
}
export const createUserRef: CreateUserRef;

export function createUser(vars: CreateUserVariables): MutationPromise<CreateUserData, CreateUserVariables>;
export function createUser(dc: DataConnect, vars: CreateUserVariables): MutationPromise<CreateUserData, CreateUserVariables>;

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

interface CreateTimeSlotRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateTimeSlotVariables): MutationRef<CreateTimeSlotData, CreateTimeSlotVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateTimeSlotVariables): MutationRef<CreateTimeSlotData, CreateTimeSlotVariables>;
  operationName: string;
}
export const createTimeSlotRef: CreateTimeSlotRef;

export function createTimeSlot(vars: CreateTimeSlotVariables): MutationPromise<CreateTimeSlotData, CreateTimeSlotVariables>;
export function createTimeSlot(dc: DataConnect, vars: CreateTimeSlotVariables): MutationPromise<CreateTimeSlotData, CreateTimeSlotVariables>;

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

interface ListTimeSlotsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListTimeSlotsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListTimeSlotsData, undefined>;
  operationName: string;
}
export const listTimeSlotsRef: ListTimeSlotsRef;

export function listTimeSlots(options?: ExecuteQueryOptions): QueryPromise<ListTimeSlotsData, undefined>;
export function listTimeSlots(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListTimeSlotsData, undefined>;

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

