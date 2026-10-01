export type TicketPriority = 'LOW' | 'MEDIUM' | 'HIGH';

export type TicketStatus = 'OPEN' | 'IN_PROGRESS' | 'DONE';

export type AssignmentMode = 'automatic' | 'manual';

export type Responsible = {
    id: number;
    name: string;
    active?: boolean;
};

export type Ticket = {
    id: number;
    title: string;
    description: string;
    requester_name: string;
    priority: TicketPriority;
    status: TicketStatus;
    next_status: TicketStatus | null;
    responsible_id: number;
    responsible: Responsible;
    opened_at: string;
    created_at: string;
    updated_at: string;
};

export type SelectOption<T extends string = string> = {
    value: T;
    label: string;
};

export type StatusOption = SelectOption<TicketStatus> & {
    board_label: string;
};
