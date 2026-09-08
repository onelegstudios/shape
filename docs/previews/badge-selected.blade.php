<x-shape::badge label="Overdue" tone="danger" as="button" :selected="true" />
<x-shape::badge label="Paid" tone="success" as="button" :selected="false" />
<x-shape::badge label="Draft" as="button" variant="outline" :selected="true" />
<x-shape::badge label="Sent" tone="info" as="button" variant="outline" :selected="false" />
<x-shape::badge label="This month" tone="brand" href="/invoices?period=month" :selected="true" />
