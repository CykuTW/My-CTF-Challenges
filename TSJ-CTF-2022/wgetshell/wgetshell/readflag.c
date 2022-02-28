#include <stdio.h>
#include <stdlib.h>
#include <time.h>

int main(void) {
    setuid(0);

    srand(time(NULL));
    int a = rand() % 100000;
    int b = rand() % 100000;
    int ans = 0;

    printf("%d + %d = ?\nans: ", a, b);
    fflush(stdout);
    scanf("%d", &ans);
    if (ans != a+b) {
        puts("bye.\n");
        return 0;
    }

    char flag[256] = {0};
    FILE* fp = fopen("/flag", "r");
    if (!fp) {
        perror("fopen");
        return 1;
    }
    if (fread(flag, 1, 256, fp) < 0) {
        perror("fread");
        return 1;
    }
    puts(flag);
    fclose(fp);
    return 0;
}