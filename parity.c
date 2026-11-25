#include <stdio.h>              // Header file for input/output

int main()
{
    int data[10], n, i;         // Array for data, n for number of bits
    int count = 0, parity;      // count stores number of 1s

    printf("Enter number of data bits: ");
    scanf("%d", &n);            // Takes number of data bits

    printf("Enter data bits: ");

    for(i = 0; i < n; i++)      // Loop to enter all data bits
    {
        scanf("%d", &data[i]);  // Stores each bit in array

        if(data[i] == 1)        // Checks whether bit is 1
            count++;             // Increases count of 1s
    }

    if(count % 2 == 0)          // Checks whether number of 1s is even
        parity = 0;             // Parity bit is 0 if count is even
    else
        parity = 1;             // Parity bit is 1 if count is odd

    printf("Parity bit = %d\n", parity);  // Displays parity bit

    printf("Enter received data including parity bit: ");

    count = 0;                  // Resets count for received data

    for(i = 0; i <= n; i++)     // Loop for data + parity bit
    {
        scanf("%d", &data[i]);  // Takes received bits

        if(data[i] == 1)        // Checks for 1
            count++;             // Counts the 1s
    }

    if(count % 2 == 0)          // Checks parity of received data
        printf("No error detected.");
    else
        printf("Error detected."); // Odd number of 1s means error

    return 0;                   // Ends the program
}