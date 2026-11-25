#include <stdio.h>

int main()
{
    int n, i, time, count, k;
    int at[20], bt[20], pr[20];
    int ct[20], tat[20], wt[20], done[20];

    printf("Enter number of processes: ");
    scanf("%d", &n);

    if(n <= 0 || n > 20)
    {
        printf("Invalid number of processes!");
        return 0;
    }

    for(i = 0; i < n; i++)
    {
        printf("Enter AT, BT and Priority of P%d: ", i + 1);
        scanf("%d%d%d", &at[i], &bt[i], &pr[i]);

        if(at[i] < 0 || bt[i] <= 0 || pr[i] < 0)
        {
            printf("Invalid input!");
            return 0;
        }
    }

    /* SJF Scheduling */
    for(i = 0; i < n; i++)
        done[i] = 0;

    time = 0;
    count = 0;

    printf("\nSJF Scheduling\n");
    printf("P\tAT\tBT\tCT\tTAT\tWT\n");

    while(count < n)
    {
        k = -1;

        for(i = 0; i < n; i++)
        {
            if(done[i] == 0 && at[i] <= time)
            {
                if(k == -1 || bt[i] < bt[k])
                    k = i;
            }
        }

        if(k == -1)
        {
            time++;
            continue;
        }

        time = time + bt[k];
        ct[k] = time;
        tat[k] = ct[k] - at[k];
        wt[k] = tat[k] - bt[k];

        done[k] = 1;
        count++;

        printf("P%d\t%d\t%d\t%d\t%d\t%d\n",
               k + 1, at[k], bt[k],
               ct[k], tat[k], wt[k]);
    }

    /* Priority Scheduling */
    for(i = 0; i < n; i++)
        done[i] = 0;

    time = 0;
    count = 0;

    printf("\nPriority Scheduling\n");
    printf("P\tAT\tBT\tPR\tCT\tTAT\tWT\n");

    while(count < n)
    {
        k = -1;

        for(i = 0; i < n; i++)
        {
            if(done[i] == 0 && at[i] <= time)
            {
                if(k == -1 || pr[i] < pr[k])
                    k = i;
            }
        }

        if(k == -1)
        {
            time++;
            continue;
        }

        time = time + bt[k];
        ct[k] = time;
        tat[k] = ct[k] - at[k];
        wt[k] = tat[k] - bt[k];

        done[k] = 1;
        count++;

        printf("P%d\t%d\t%d\t%d\t%d\t%d\t%d\n",
               k + 1, at[k], bt[k], pr[k],
               ct[k], tat[k], wt[k]);
    }

    return 0;
}