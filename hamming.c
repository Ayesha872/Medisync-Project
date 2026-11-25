
#include <stdio.h>
#include <string.h>

int main()
{
    char str1[100], str2[100];
    int i, distance = 0;
    int valid = 1;

    printf("Enter first binary string: ");
    scanf("%99s", str1);

    printf("Enter second binary string: ");
    scanf("%99s", str2);

    if (strlen(str1) != strlen(str2))
    {
        printf("Strings must have equal length.");
    }
    else
    {
        for (i = 0; str1[i] != '\0'; i++)
        {
            if ((str1[i] != '0' && str1[i] != '1') ||
                (str2[i] != '0' && str2[i] != '1'))
            {
                valid = 0;
                break;
            }
        }

        if (valid == 0)
        {
            printf("Invalid binary string.");
        }
        else
        {
            for (i = 0; str1[i] != '\0'; i++)
            {
                if (str1[i] != str2[i])
                {
                    distance++;
                }
            }

            printf("Hamming Distance = %d", distance);
        }
    }

    return 0;
}
