#include <stdio.h>
#include <stdlib.h>

int main()
{
    char s[50];
    FILE *fd = fopen("/here_is_your_flag","r");
    fgets(s, 50, fd);
    printf("%s\n", s);
    fclose(fd);
    return 0;
}