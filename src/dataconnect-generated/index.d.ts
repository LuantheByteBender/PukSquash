import { ConnectorConfig, DataConnect, QueryRef, QueryPromise, ExecuteQueryOptions, MutationRef, MutationPromise } from 'firebase/data-connect';

export const connectorConfig: ConnectorConfig;

export type TimestampString = string;
export type UUIDString = string;
export type Int64String = string;
export type DateString = string;




export interface AddBookingPlayerData {
  bookingPlayer_insert: BookingPlayer_Key;
}

export interface AddBookingPlayerVariables {
  bookingId: UUIDString;
  userId: UUIDString;
  playerPosition: number;
  joinedAt: TimestampString;
}

export interface BookingPlayer_Key {
  id: UUIDString;
  __typename?: 'BookingPlayer_Key';
}

export interface Booking_Key {
  id: UUIDString;
  __typename?: 'Booking_Key';
}

export interface CancelBookingData {
  booking_update?: Booking_Key | null;
}

export interface CancelBookingVariables {
  id: UUIDString;
  cancelledAt: TimestampString;
}

export interface CoachAvailability_Key {
  coachId: UUIDString;
  timeSlotId: UUIDString;
  availabilityDate: DateString;
  __typename?: 'CoachAvailability_Key';
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
  timeSlotId: UUIDString;
  createdById: UUIDString;
  coachId?: UUIDString | null;
  bookingDate: DateString;
  bookingType: string;
  bookedAt: TimestampString;
}

export interface CreateCoachAvailabilityData {
  coachAvailability_insert: CoachAvailability_Key;
}

export interface CreateCoachAvailabilityVariables {
  coachId: UUIDString;
  timeSlotId: UUIDString;
  availabilityDate: DateString;
}

export interface CreateCourtData {
  court_insert: Court_Key;
}

export interface CreateCourtVariables {
  name: string;
  description?: string | null;
  status?: string | null;
}

export interface CreateTimeSlotData {
  timeSlot_insert: TimeSlot_Key;
}

export interface CreateTimeSlotVariables {
  startTime: string;
  endTime: string;
  displayOrder: number;
  isActive?: boolean | null;
}

export interface DeleteCoachAvailabilityForSlotData {
  coachAvailability_delete?: CoachAvailability_Key | null;
}

export interface DeleteCoachAvailabilityForSlotVariables {
  coachId: UUIDString;
  timeSlotId: UUIDString;
  availabilityDate: DateString;
}

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
      firebaseUid: string;
    };
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

export interface GetAvailabilityDataVariables {
  bookingDate: DateString;
}

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

export interface GetAvailableCoachesVariables {
  availabilityDate: DateString;
  timeSlotId: UUIDString;
}

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

export interface GetBookingByIdVariables {
  id: UUIDString;
}

export interface GetBookingForCellData {
  bookings: ({
    id: UUIDString;
  } & Booking_Key)[];
}

export interface GetBookingForCellVariables {
  bookingDate: DateString;
  courtId: UUIDString;
  timeSlotId: UUIDString;
}

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

export interface GetBookingPlayersVariables {
  bookingId: UUIDString;
}

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

export interface GetBookingsByStatusVariables {
  status: string;
}

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

export interface GetBookingsByUserForSlotData {
  bookings: ({
    id: UUIDString;
  } & Booking_Key)[];
}

export interface GetBookingsByUserForSlotVariables {
  userId: UUIDString;
  bookingDate: DateString;
  timeSlotId: UUIDString;
}

export interface GetBookingsByUserVariables {
  userId: UUIDString;
  status?: string | null;
}

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

export interface GetBookingsForDateVariables {
  bookingDate: DateString;
}

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

export interface GetBookingsForTimeSlotVariables {
  bookingDate: DateString;
  timeSlotId: UUIDString;
}

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

export interface GetCoachAvailabilityForDateVariables {
  coachId: UUIDString;
  availabilityDate: DateString;
}

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

export interface GetCoachAvailabilityNoticeBoardVariables {
  availabilityDate: DateString;
}

export interface GetConfirmedBookingForCoachSlotData {
  bookings: ({
    id: UUIDString;
  } & Booking_Key)[];
}

export interface GetConfirmedBookingForCoachSlotVariables {
  coachId: UUIDString;
  bookingDate: DateString;
  timeSlotId: UUIDString;
}

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

export interface GetConfirmedBookingsByCoachVariables {
  coachId: UUIDString;
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

export interface GetUserByFirebaseUidVariables {
  firebaseUid: string;
}

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

export interface GetUserByIdVariables {
  id: UUIDString;
}

export interface IsCoachAvailableData {
  coachAvailabilities: ({
    coachId: UUIDString;
  })[];
}

export interface IsCoachAvailableVariables {
  availabilityDate: DateString;
  timeSlotId: UUIDString;
  coachId: UUIDString;
}

export interface LeaveBookingData {
  bookingPlayer_deleteMany: number;
}

export interface LeaveBookingVariables {
  bookingId: UUIDString;
  userId: UUIDString;
}

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

export interface ListActiveTimeSlotsData {
  timeSlots: ({
    id: UUIDString;
    startTime: string;
    endTime: string;
    displayOrder: number;
    isActive: boolean;
  } & TimeSlot_Key)[];
}

export interface TimeSlot_Key {
  id: UUIDString;
  __typename?: 'TimeSlot_Key';
}

export interface UpdateCourtStatusData {
  court_update?: Court_Key | null;
}

export interface UpdateCourtStatusVariables {
  id: UUIDString;
  status: string;
}

export interface UpdateUserProfileData {
  user_updateMany: number;
}

export interface UpdateUserProfileVariables {
  firebaseUid: string;
  fullName: string;
  studentNumber?: string | null;
}

export interface UpsertUserData {
  user_insert: User_Key;
}

export interface UpsertUserVariables {
  firebaseUid: string;
  fullName: string;
  email: string;
  studentNumber?: string | null;
  role?: string | null;
  status?: string | null;
}

export interface User_Key {
  id: UUIDString;
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

interface UpdateUserProfileRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpdateUserProfileVariables): MutationRef<UpdateUserProfileData, UpdateUserProfileVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: UpdateUserProfileVariables): MutationRef<UpdateUserProfileData, UpdateUserProfileVariables>;
  operationName: string;
}
export const updateUserProfileRef: UpdateUserProfileRef;

export function updateUserProfile(vars: UpdateUserProfileVariables): MutationPromise<UpdateUserProfileData, UpdateUserProfileVariables>;
export function updateUserProfile(dc: DataConnect, vars: UpdateUserProfileVariables): MutationPromise<UpdateUserProfileData, UpdateUserProfileVariables>;

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

interface UpdateCourtStatusRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpdateCourtStatusVariables): MutationRef<UpdateCourtStatusData, UpdateCourtStatusVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: UpdateCourtStatusVariables): MutationRef<UpdateCourtStatusData, UpdateCourtStatusVariables>;
  operationName: string;
}
export const updateCourtStatusRef: UpdateCourtStatusRef;

export function updateCourtStatus(vars: UpdateCourtStatusVariables): MutationPromise<UpdateCourtStatusData, UpdateCourtStatusVariables>;
export function updateCourtStatus(dc: DataConnect, vars: UpdateCourtStatusVariables): MutationPromise<UpdateCourtStatusData, UpdateCourtStatusVariables>;

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

interface CancelBookingRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CancelBookingVariables): MutationRef<CancelBookingData, CancelBookingVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CancelBookingVariables): MutationRef<CancelBookingData, CancelBookingVariables>;
  operationName: string;
}
export const cancelBookingRef: CancelBookingRef;

export function cancelBooking(vars: CancelBookingVariables): MutationPromise<CancelBookingData, CancelBookingVariables>;
export function cancelBooking(dc: DataConnect, vars: CancelBookingVariables): MutationPromise<CancelBookingData, CancelBookingVariables>;

interface AddBookingPlayerRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: AddBookingPlayerVariables): MutationRef<AddBookingPlayerData, AddBookingPlayerVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: AddBookingPlayerVariables): MutationRef<AddBookingPlayerData, AddBookingPlayerVariables>;
  operationName: string;
}
export const addBookingPlayerRef: AddBookingPlayerRef;

export function addBookingPlayer(vars: AddBookingPlayerVariables): MutationPromise<AddBookingPlayerData, AddBookingPlayerVariables>;
export function addBookingPlayer(dc: DataConnect, vars: AddBookingPlayerVariables): MutationPromise<AddBookingPlayerData, AddBookingPlayerVariables>;

interface LeaveBookingRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: LeaveBookingVariables): MutationRef<LeaveBookingData, LeaveBookingVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: LeaveBookingVariables): MutationRef<LeaveBookingData, LeaveBookingVariables>;
  operationName: string;
}
export const leaveBookingRef: LeaveBookingRef;

export function leaveBooking(vars: LeaveBookingVariables): MutationPromise<LeaveBookingData, LeaveBookingVariables>;
export function leaveBooking(dc: DataConnect, vars: LeaveBookingVariables): MutationPromise<LeaveBookingData, LeaveBookingVariables>;

interface CreateCoachAvailabilityRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateCoachAvailabilityVariables): MutationRef<CreateCoachAvailabilityData, CreateCoachAvailabilityVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateCoachAvailabilityVariables): MutationRef<CreateCoachAvailabilityData, CreateCoachAvailabilityVariables>;
  operationName: string;
}
export const createCoachAvailabilityRef: CreateCoachAvailabilityRef;

export function createCoachAvailability(vars: CreateCoachAvailabilityVariables): MutationPromise<CreateCoachAvailabilityData, CreateCoachAvailabilityVariables>;
export function createCoachAvailability(dc: DataConnect, vars: CreateCoachAvailabilityVariables): MutationPromise<CreateCoachAvailabilityData, CreateCoachAvailabilityVariables>;

interface DeleteCoachAvailabilityForSlotRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: DeleteCoachAvailabilityForSlotVariables): MutationRef<DeleteCoachAvailabilityForSlotData, DeleteCoachAvailabilityForSlotVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: DeleteCoachAvailabilityForSlotVariables): MutationRef<DeleteCoachAvailabilityForSlotData, DeleteCoachAvailabilityForSlotVariables>;
  operationName: string;
}
export const deleteCoachAvailabilityForSlotRef: DeleteCoachAvailabilityForSlotRef;

export function deleteCoachAvailabilityForSlot(vars: DeleteCoachAvailabilityForSlotVariables): MutationPromise<DeleteCoachAvailabilityForSlotData, DeleteCoachAvailabilityForSlotVariables>;
export function deleteCoachAvailabilityForSlot(dc: DataConnect, vars: DeleteCoachAvailabilityForSlotVariables): MutationPromise<DeleteCoachAvailabilityForSlotData, DeleteCoachAvailabilityForSlotVariables>;

interface GetUserByFirebaseUidRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetUserByFirebaseUidVariables): QueryRef<GetUserByFirebaseUidData, GetUserByFirebaseUidVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetUserByFirebaseUidVariables): QueryRef<GetUserByFirebaseUidData, GetUserByFirebaseUidVariables>;
  operationName: string;
}
export const getUserByFirebaseUidRef: GetUserByFirebaseUidRef;

export function getUserByFirebaseUid(vars: GetUserByFirebaseUidVariables, options?: ExecuteQueryOptions): QueryPromise<GetUserByFirebaseUidData, GetUserByFirebaseUidVariables>;
export function getUserByFirebaseUid(dc: DataConnect, vars: GetUserByFirebaseUidVariables, options?: ExecuteQueryOptions): QueryPromise<GetUserByFirebaseUidData, GetUserByFirebaseUidVariables>;

interface GetBookingsByUserForSlotRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsByUserForSlotVariables): QueryRef<GetBookingsByUserForSlotData, GetBookingsByUserForSlotVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetBookingsByUserForSlotVariables): QueryRef<GetBookingsByUserForSlotData, GetBookingsByUserForSlotVariables>;
  operationName: string;
}
export const getBookingsByUserForSlotRef: GetBookingsByUserForSlotRef;

export function getBookingsByUserForSlot(vars: GetBookingsByUserForSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByUserForSlotData, GetBookingsByUserForSlotVariables>;
export function getBookingsByUserForSlot(dc: DataConnect, vars: GetBookingsByUserForSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByUserForSlotData, GetBookingsByUserForSlotVariables>;

interface GetBookingForCellRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingForCellVariables): QueryRef<GetBookingForCellData, GetBookingForCellVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetBookingForCellVariables): QueryRef<GetBookingForCellData, GetBookingForCellVariables>;
  operationName: string;
}
export const getBookingForCellRef: GetBookingForCellRef;

export function getBookingForCell(vars: GetBookingForCellVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingForCellData, GetBookingForCellVariables>;
export function getBookingForCell(dc: DataConnect, vars: GetBookingForCellVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingForCellData, GetBookingForCellVariables>;

interface GetUserByIdRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetUserByIdVariables): QueryRef<GetUserByIdData, GetUserByIdVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetUserByIdVariables): QueryRef<GetUserByIdData, GetUserByIdVariables>;
  operationName: string;
}
export const getUserByIdRef: GetUserByIdRef;

export function getUserById(vars: GetUserByIdVariables, options?: ExecuteQueryOptions): QueryPromise<GetUserByIdData, GetUserByIdVariables>;
export function getUserById(dc: DataConnect, vars: GetUserByIdVariables, options?: ExecuteQueryOptions): QueryPromise<GetUserByIdData, GetUserByIdVariables>;

interface ListActiveCourtsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListActiveCourtsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListActiveCourtsData, undefined>;
  operationName: string;
}
export const listActiveCourtsRef: ListActiveCourtsRef;

export function listActiveCourts(options?: ExecuteQueryOptions): QueryPromise<ListActiveCourtsData, undefined>;
export function listActiveCourts(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListActiveCourtsData, undefined>;

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

interface ListActiveTimeSlotsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListActiveTimeSlotsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListActiveTimeSlotsData, undefined>;
  operationName: string;
}
export const listActiveTimeSlotsRef: ListActiveTimeSlotsRef;

export function listActiveTimeSlots(options?: ExecuteQueryOptions): QueryPromise<ListActiveTimeSlotsData, undefined>;
export function listActiveTimeSlots(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListActiveTimeSlotsData, undefined>;

interface GetAvailabilityDataRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetAvailabilityDataVariables): QueryRef<GetAvailabilityDataData, GetAvailabilityDataVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetAvailabilityDataVariables): QueryRef<GetAvailabilityDataData, GetAvailabilityDataVariables>;
  operationName: string;
}
export const getAvailabilityDataRef: GetAvailabilityDataRef;

export function getAvailabilityData(vars: GetAvailabilityDataVariables, options?: ExecuteQueryOptions): QueryPromise<GetAvailabilityDataData, GetAvailabilityDataVariables>;
export function getAvailabilityData(dc: DataConnect, vars: GetAvailabilityDataVariables, options?: ExecuteQueryOptions): QueryPromise<GetAvailabilityDataData, GetAvailabilityDataVariables>;

interface GetBookingsByStatusRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsByStatusVariables): QueryRef<GetBookingsByStatusData, GetBookingsByStatusVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetBookingsByStatusVariables): QueryRef<GetBookingsByStatusData, GetBookingsByStatusVariables>;
  operationName: string;
}
export const getBookingsByStatusRef: GetBookingsByStatusRef;

export function getBookingsByStatus(vars: GetBookingsByStatusVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByStatusData, GetBookingsByStatusVariables>;
export function getBookingsByStatus(dc: DataConnect, vars: GetBookingsByStatusVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByStatusData, GetBookingsByStatusVariables>;

interface GetBookingsForDateRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsForDateVariables): QueryRef<GetBookingsForDateData, GetBookingsForDateVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetBookingsForDateVariables): QueryRef<GetBookingsForDateData, GetBookingsForDateVariables>;
  operationName: string;
}
export const getBookingsForDateRef: GetBookingsForDateRef;

export function getBookingsForDate(vars: GetBookingsForDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsForDateData, GetBookingsForDateVariables>;
export function getBookingsForDate(dc: DataConnect, vars: GetBookingsForDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsForDateData, GetBookingsForDateVariables>;

interface GetBookingByIdRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingByIdVariables): QueryRef<GetBookingByIdData, GetBookingByIdVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetBookingByIdVariables): QueryRef<GetBookingByIdData, GetBookingByIdVariables>;
  operationName: string;
}
export const getBookingByIdRef: GetBookingByIdRef;

export function getBookingById(vars: GetBookingByIdVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingByIdData, GetBookingByIdVariables>;
export function getBookingById(dc: DataConnect, vars: GetBookingByIdVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingByIdData, GetBookingByIdVariables>;

interface GetBookingsForTimeSlotRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsForTimeSlotVariables): QueryRef<GetBookingsForTimeSlotData, GetBookingsForTimeSlotVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetBookingsForTimeSlotVariables): QueryRef<GetBookingsForTimeSlotData, GetBookingsForTimeSlotVariables>;
  operationName: string;
}
export const getBookingsForTimeSlotRef: GetBookingsForTimeSlotRef;

export function getBookingsForTimeSlot(vars: GetBookingsForTimeSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsForTimeSlotData, GetBookingsForTimeSlotVariables>;
export function getBookingsForTimeSlot(dc: DataConnect, vars: GetBookingsForTimeSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsForTimeSlotData, GetBookingsForTimeSlotVariables>;

interface GetBookingsByUserRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingsByUserVariables): QueryRef<GetBookingsByUserData, GetBookingsByUserVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetBookingsByUserVariables): QueryRef<GetBookingsByUserData, GetBookingsByUserVariables>;
  operationName: string;
}
export const getBookingsByUserRef: GetBookingsByUserRef;

export function getBookingsByUser(vars: GetBookingsByUserVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByUserData, GetBookingsByUserVariables>;
export function getBookingsByUser(dc: DataConnect, vars: GetBookingsByUserVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingsByUserData, GetBookingsByUserVariables>;

interface GetBookingPlayersRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetBookingPlayersVariables): QueryRef<GetBookingPlayersData, GetBookingPlayersVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetBookingPlayersVariables): QueryRef<GetBookingPlayersData, GetBookingPlayersVariables>;
  operationName: string;
}
export const getBookingPlayersRef: GetBookingPlayersRef;

export function getBookingPlayers(vars: GetBookingPlayersVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingPlayersData, GetBookingPlayersVariables>;
export function getBookingPlayers(dc: DataConnect, vars: GetBookingPlayersVariables, options?: ExecuteQueryOptions): QueryPromise<GetBookingPlayersData, GetBookingPlayersVariables>;

interface GetAvailableCoachesRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetAvailableCoachesVariables): QueryRef<GetAvailableCoachesData, GetAvailableCoachesVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetAvailableCoachesVariables): QueryRef<GetAvailableCoachesData, GetAvailableCoachesVariables>;
  operationName: string;
}
export const getAvailableCoachesRef: GetAvailableCoachesRef;

export function getAvailableCoaches(vars: GetAvailableCoachesVariables, options?: ExecuteQueryOptions): QueryPromise<GetAvailableCoachesData, GetAvailableCoachesVariables>;
export function getAvailableCoaches(dc: DataConnect, vars: GetAvailableCoachesVariables, options?: ExecuteQueryOptions): QueryPromise<GetAvailableCoachesData, GetAvailableCoachesVariables>;

interface IsCoachAvailableRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: IsCoachAvailableVariables): QueryRef<IsCoachAvailableData, IsCoachAvailableVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: IsCoachAvailableVariables): QueryRef<IsCoachAvailableData, IsCoachAvailableVariables>;
  operationName: string;
}
export const isCoachAvailableRef: IsCoachAvailableRef;

export function isCoachAvailable(vars: IsCoachAvailableVariables, options?: ExecuteQueryOptions): QueryPromise<IsCoachAvailableData, IsCoachAvailableVariables>;
export function isCoachAvailable(dc: DataConnect, vars: IsCoachAvailableVariables, options?: ExecuteQueryOptions): QueryPromise<IsCoachAvailableData, IsCoachAvailableVariables>;

interface GetCoachAvailabilityForDateRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetCoachAvailabilityForDateVariables): QueryRef<GetCoachAvailabilityForDateData, GetCoachAvailabilityForDateVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetCoachAvailabilityForDateVariables): QueryRef<GetCoachAvailabilityForDateData, GetCoachAvailabilityForDateVariables>;
  operationName: string;
}
export const getCoachAvailabilityForDateRef: GetCoachAvailabilityForDateRef;

export function getCoachAvailabilityForDate(vars: GetCoachAvailabilityForDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetCoachAvailabilityForDateData, GetCoachAvailabilityForDateVariables>;
export function getCoachAvailabilityForDate(dc: DataConnect, vars: GetCoachAvailabilityForDateVariables, options?: ExecuteQueryOptions): QueryPromise<GetCoachAvailabilityForDateData, GetCoachAvailabilityForDateVariables>;

interface GetConfirmedBookingForCoachSlotRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetConfirmedBookingForCoachSlotVariables): QueryRef<GetConfirmedBookingForCoachSlotData, GetConfirmedBookingForCoachSlotVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetConfirmedBookingForCoachSlotVariables): QueryRef<GetConfirmedBookingForCoachSlotData, GetConfirmedBookingForCoachSlotVariables>;
  operationName: string;
}
export const getConfirmedBookingForCoachSlotRef: GetConfirmedBookingForCoachSlotRef;

export function getConfirmedBookingForCoachSlot(vars: GetConfirmedBookingForCoachSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingForCoachSlotData, GetConfirmedBookingForCoachSlotVariables>;
export function getConfirmedBookingForCoachSlot(dc: DataConnect, vars: GetConfirmedBookingForCoachSlotVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingForCoachSlotData, GetConfirmedBookingForCoachSlotVariables>;

interface GetConfirmedBookingsByCoachRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetConfirmedBookingsByCoachVariables): QueryRef<GetConfirmedBookingsByCoachData, GetConfirmedBookingsByCoachVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetConfirmedBookingsByCoachVariables): QueryRef<GetConfirmedBookingsByCoachData, GetConfirmedBookingsByCoachVariables>;
  operationName: string;
}
export const getConfirmedBookingsByCoachRef: GetConfirmedBookingsByCoachRef;

export function getConfirmedBookingsByCoach(vars: GetConfirmedBookingsByCoachVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingsByCoachData, GetConfirmedBookingsByCoachVariables>;
export function getConfirmedBookingsByCoach(dc: DataConnect, vars: GetConfirmedBookingsByCoachVariables, options?: ExecuteQueryOptions): QueryPromise<GetConfirmedBookingsByCoachData, GetConfirmedBookingsByCoachVariables>;

interface GetCoachAvailabilityNoticeBoardRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: GetCoachAvailabilityNoticeBoardVariables): QueryRef<GetCoachAvailabilityNoticeBoardData, GetCoachAvailabilityNoticeBoardVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: GetCoachAvailabilityNoticeBoardVariables): QueryRef<GetCoachAvailabilityNoticeBoardData, GetCoachAvailabilityNoticeBoardVariables>;
  operationName: string;
}
export const getCoachAvailabilityNoticeBoardRef: GetCoachAvailabilityNoticeBoardRef;

export function getCoachAvailabilityNoticeBoard(vars: GetCoachAvailabilityNoticeBoardVariables, options?: ExecuteQueryOptions): QueryPromise<GetCoachAvailabilityNoticeBoardData, GetCoachAvailabilityNoticeBoardVariables>;
export function getCoachAvailabilityNoticeBoard(dc: DataConnect, vars: GetCoachAvailabilityNoticeBoardVariables, options?: ExecuteQueryOptions): QueryPromise<GetCoachAvailabilityNoticeBoardData, GetCoachAvailabilityNoticeBoardVariables>;

