#include <stdio.h>

int main()
{
    int n, i;
    int at[20], bt[20], ct[20], wt[20], tat[20];
    int rem[20];
    int queue[100];
    int visited[20] = {0};
    int front = 0, rear = 0;
    int time = 0, quantum;
    int p, run;
    float avg_wt, avg_tat;

    printf("Enter number of processes: ");
    scanf("%d", &n);

    printf("\nEnter Arrival Time and Burst Time:\n");

    for(i = 0; i < n; i++)
    {
        printf("P%d Arrival Time: ", i + 1);
        scanf("%d", &at[i]);

        printf("P%d Burst Time: ", i + 1);
        scanf("%d", &bt[i]);

        rem[i] = bt[i];
    }

    /* FCFS Scheduling */

    time = 0;
    avg_wt = 0;
    avg_tat = 0;

    printf("\n\n----- FCFS Scheduling -----\n");
    printf("\nProcess\tAT\tBT\tCT\tWT\tTAT\n");

    for(i = 0; i < n; i++)
    {
        if(time < at[i])
        {
            time = at[i];
        }

        time = time + bt[i];

        ct[i] = time;
        tat[i] = ct[i] - at[i];
        wt[i] = tat[i] - bt[i];

        avg_wt = avg_wt + wt[i];
        avg_tat = avg_tat + tat[i];

        printf("P%d\t%d\t%d\t%d\t%d\t%d\n",
               i + 1, at[i], bt[i], ct[i], wt[i], tat[i]);
    }

    avg_wt = avg_wt / n;
    avg_tat = avg_tat / n;

    printf("\nAverage Waiting Time = %.2f", avg_wt);
    printf("\nAverage Turnaround Time = %.2f\n", avg_tat);


    /* Round Robin Scheduling */

    printf("\nEnter Time Quantum for Round Robin: ");
    scanf("%d", &quantum);

    for(i = 0; i < n; i++)
    {
        rem[i] = bt[i];
        ct[i] = 0;
        visited[i] = 0;
    }

    front = 0;
    rear = 0;
    time = 0;

    printf("\n\n----- Round Robin Scheduling -----\n");
    printf("\nExecution Order:\n");

    while(1)
    {
        /* Add newly arrived processes */

        for(i = 0; i < n; i++)
        {
            if(at[i] <= time && rem[i] > 0 && visited[i] == 0)
            {
                queue[rear] = i;
                rear++;
                visited[i] = 1;
            }
        }

        /* If queue is empty, move to next process */

        if(front == rear)
        {
            int next = -1;

            for(i = 0; i < n; i++)
            {
                if(rem[i] > 0 && visited[i] == 0)
                {
                    if(next == -1 || at[i] < at[next])
                    {
                        next = i;
                    }
                }
            }

            if(next == -1)
            {
                break;
            }

            time = at[next];

            for(i = 0; i < n; i++)
            {
                if(at[i] <= time && rem[i] > 0 && visited[i] == 0)
                {
                    queue[rear] = i;
                    rear++;
                    visited[i] = 1;
                }
            }
        }

        p = queue[front];
        front++;

        printf("P%d ", p + 1);

        if(rem[p] > quantum)
        {
            run = quantum;
        }
        else
        {
            run = rem[p];
        }

        time = time + run;
        rem[p] = rem[p] - run;

        /* Add processes which arrived during execution */

        for(i = 0; i < n; i++)
        {
            if(at[i] <= time && rem[i] > 0 && visited[i] == 0)
            {
                queue[rear] = i;
                rear++;
                visited[i] = 1;
            }
        }

        if(rem[p] > 0)
        {
            queue[rear] = p;
            rear++;
        }
        else
        {
            ct[p] = time;
        }
    }

    avg_wt = 0;
    avg_tat = 0;

    printf("\n\nProcess\tAT\tBT\tCT\tWT\tTAT\n");

    for(i = 0; i < n; i++)
    {
        tat[i] = ct[i] - at[i];
        wt[i] = tat[i] - bt[i];

        avg_wt = avg_wt + wt[i];
        avg_tat = avg_tat + tat[i];

        printf("P%d\t%d\t%d\t%d\t%d\t%d\n",
               i + 1, at[i], bt[i], ct[i], wt[i], tat[i]);
    }

    avg_wt = avg_wt / n;
    avg_tat = avg_tat / n;

    printf("\nAverage Waiting Time = %.2f", avg_wt);
    printf("\nAverage Turnaround Time = %.2f\n", avg_tat);

    return 0;
}