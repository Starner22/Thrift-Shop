<?php
require_once __DIR__ . '/AbstractSubject.php';

class TicketSubject extends AbstractSubject {
    private PDO $db;
    private int $ticketID;

    public function __construct(PDO $db, int $ticketID) {
        $this->db = $db;
        $this->ticketID = $ticketID;
    }

    public function updateStatus(string $status): void {
        $this->db->prepare("UPDATE supportticket SET status=:s WHERE ticketID=:id")
                 ->execute([':s'=>$status, ':id'=>$this->ticketID]);

        $event = [
            'title' => "Support Ticket Update",
            'message' => "Your ticket #{$this->ticketID} status is now {$status}.",
            'eventType' => 'ticket_update',
            'refType' => 'ticket',
            'refID' => $this->ticketID
        ];
        $this->notify($event);
    }
}
