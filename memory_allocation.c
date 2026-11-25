#include <stdio.h>
#include <string.h>

int main()
{
    int b[20], f[20], allocation[20];
    int nb, nf, i, j, index;
    char method[10];

    printf("Enter number of memory blocks: ");
    scanf("%d", &nb);

    printf("Enter number of files: ");
    scanf("%d", &nf);

    printf("Enter sizes of memory blocks:\n");
    for(i = 0; i < nb; i++)
    {
        printf("Block %d: ", i + 1);
        scanf("%d", &b[i]);
    }

    printf("Enter sizes of files:\n");
    for(i = 0; i < nf; i++)
    {
        printf("File %d: ", i + 1);
        scanf("%d", &f[i]);
    }

    printf("Enter method (first/best/worst): ");
    scanf("%9s", method);

    for(i = 0; i < nf; i++)
        allocation[i] = -1;

    if(strcmp(method, "first") == 0)
    {
        for(i = 0; i < nf; i++)
        {
            for(j = 0; j < nb; j++)
            {
                if(b[j] >= f[i])
                {
                    allocation[i] = j;
                    b[j] -= f[i];
                    break;
                }
            }
        }
    }
    else if(strcmp(method, "best") == 0)
    {
        for(i = 0; i < nf; i++)
        {
            index = -1;

            for(j = 0; j < nb; j++)
            {
                if(b[j] >= f[i] &&
                   (index == -1 || b[j] < b[index]))
                    index = j;
            }

            if(index != -1)
            {
                allocation[i] = index;
                b[index] -= f[i];
            }
        }
    }
    else if(strcmp(method, "worst") == 0)
    {
        for(i = 0; i < nf; i++)
        {
            index = -1;

            for(j = 0; j < nb; j++)
            {
                if(b[j] >= f[i] &&
                   (index == -1 || b[j] > b[index]))
                    index = j;
            }

            if(index != -1)
            {
                allocation[i] = index;
                b[index] -= f[i];
            }
        }
    }
    else
    {
        printf("Invalid allocation method!\n");
        return 0;
    }

    printf("\nFile No.\tFile Size\tBlock No.\n");

    for(i = 0; i < nf; i++)
    {
        printf("%d\t\t%d\t\t", i + 1, f[i]);

        if(allocation[i] != -1)
            printf("%d\n", allocation[i] + 1);
        else
            printf("Not Allocated\n");
    }

    return 0;
}