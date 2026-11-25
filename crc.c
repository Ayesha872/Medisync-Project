
#include <stdio.h>
#include <string.h>

int main()
{
    char data[100], generator[20], temp[120];
    char transmitted[120], received[120];
    int i, j, dataLen, genLen, error = 0;

    printf("Enter data: ");
    scanf("%99s", data);

    printf("Enter generator: ");
    scanf("%19s", generator);

    dataLen = strlen(data);
    genLen = strlen(generator);

    strcpy(temp, data);

    for(i = 0; i < genLen - 1; i++)
        temp[dataLen + i] = '0';

    temp[dataLen + genLen - 1] = '\0';

    for(i = 0; i < dataLen; i++)
    {
        if(temp[i] == '1')
        {
            for(j = 0; j < genLen; j++)
            {
                if(temp[i + j] == generator[j])
                    temp[i + j] = '0';
                else
                    temp[i + j] = '1';
            }
        }
    }

    printf("\nCRC = ");

    for(i = dataLen; i < dataLen + genLen - 1; i++)
        printf("%c", temp[i]);

    strcpy(transmitted, data);

    for(i = 0; i < genLen - 1; i++)
        transmitted[dataLen + i] = temp[dataLen + i];

    transmitted[dataLen + genLen - 1] = '\0';

    printf("\nTransmitted Data = %s\n", transmitted);

    printf("Enter received data: ");
    scanf("%119s", received);

    if(strlen(received) != strlen(transmitted))
    {
        printf("Invalid received data length\n");
        return 1;
    }

    strcpy(temp, received);

    for(i = 0; i < (int)strlen(received) - genLen + 1; i++)
    {
        if(temp[i] == '1')
        {
            for(j = 0; j < genLen; j++)
            {
                if(temp[i + j] == generator[j])
                    temp[i + j] = '0';
                else
                    temp[i + j] = '1';
            }
        }
    }

    for(i = (int)strlen(received) - genLen + 1;
        i < (int)strlen(received); i++)
    {
        if(temp[i] != '0')
            error = 1;
    }

    if(error == 0)
        printf("No error detected\n");
    else
        printf("Error detected\n");

    return 0;
}
