<!-- FEATURES SECTION -->
<section class="py-12 sm:py-24 bg-[rgba(233,243,253,0.7)]">
    <div class="container mx-auto">
        <!-- Section Header -->
        <h2 class="sr sr--fade-up w-full text-[30px] font-space sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-center text-home-heading mb-3 sm:mb-4 flex-none order-0 self-stretch flex-grow-0" style="--sr-delay: 0ms"><?php echo e(__('home_features.heading')); ?></h2>
        <div class="sr sr--fade-up text-center mb-8 sm:mb-12 text-home-body text-sm md:text-base max-w-3xl mx-auto leading-relaxed font-plus" style="--sr-delay: 50ms">
            <?php echo e(__('home_features.intro')); ?>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-3 sm:gap-8 gap-4">

            <div class="sr sr--fade-up rounded-home-lg p-[2px] bg-gradient-to-r from-home-accent to-home-primary" style="--sr-delay: 0ms">
                <div class="bg-white rounded-home-card w-full h-full p-6 hover:shadow-md transition-all duration-300">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 gap-2">
                        <div class="flex justify-start">
                            <div
                                class="w-[36px] h-[36px] flex items-center justify-center bg-home-surface-light rounded-home-sm text-home-primary sm:mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-zap-icon lucide-zap">
                                    <path
                                        d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="font-medium text-[20px] text-home-primary text-left font-plus"><?php echo e(__('home_features.ultra_fast_title')); ?></h3>
                    </div>

                    <p class="mt-1 text-sm text-gray-600 leading-relaxed font-plus">
                        <?php echo e(__('home_features.ultra_fast_desc')); ?>
                    </p>
                </div>
            </div>

            <!-- Feature 2: Portable -->
            <div class="sr sr--fade-up p-[2px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg" style="--sr-delay: 70ms">
                <div class="bg-white rounded-home-card w-full h-full p-6 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 gap-2">
                        <div class="flex justify-start">
                            <svg width="36" height="36" class="sm:mr-2" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <rect width="36" height="36" rx="2.48276" fill="var(--home-surface-light)" />
                                <rect x="6.20679" y="6.20703" width="23.5862" height="23.5862"
                                    fill="url(#pattern0_186_6775)" />
                                <defs>
                                    <pattern id="pattern0_186_6775" patternContentUnits="objectBoundingBox" width="1"
                                        height="1">
                                        <use xlink:href="#image0_186_6775" transform="scale(0.01)" />
                                    </pattern>
                                    <image id="image0_186_6775" width="100" height="100" preserveAspectRatio="none"
                                        xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAACdklEQVR4nO2du4oUQRRA6w98xAaKr52qWQ1MNPEBsj4wEMRURDQQMz9BA3fn3maD1Z9YH3+ign9gKn2rZw0VHJmFNXRnpnu3b/ecAycaaJh7aKqnu6kJAQAAAAAAAAAAAAAAAP4Ri/HxYZGvxcIeenNQ2N00Gp8Oy8CK/jgX1T5Gtd9J88SzUfK3gVb3Q19Jo3Itaf7Z9qDT/Gro45nR0RiT3bNF84vQJ6Lap7aHmuoFGa++GR8LfVnAu7BmpH0cavkk9IFUVDfaHmZq5Cyxd6EPpKJ80PYwUzN+T5K329HeRq0eX9ysjtYOMr22dzDMSR+MajmO7BlB1JdR7TVB1JfTOwoEUUeKfSGI+vKCVKcIop60W8sbRPJ2aIH/zW/6WaMH7JRCEF8KQXwpBPGlEMSXQpBOBlldL0/M8ux+OLIrBNGDDzLz923geASZAYIQZD4IkgmSCEKQPQhCkPkgSCZIIghB9lgpysuzvNoT1V6GGSCI1gvSNARRgnRDIYgvhSC+FIL4UgjiS+Gy15fCD0NfCkF8KQTxpRDEl0IQXwpBfCkE8aUQxJdCEF8Kr5L6UriX5UshiC+FIL4UgvhSCOJLIQjqPo7KNYKoH89vVicJol60z3PHIEg+GMX+xMJuE0SdKPZqoRgEyU2HsKT2NNSBTTBzfdW2UpEfXVrPR2rF2A0i1XUHw5zU17ZCH5hubRrFfrU/0FzL6TatoS9EyR86HUOsamTPXC8MNnbOJsk7bQ82Le7z0DcGo/JmF6NEyRuhr8RifCZpft+NNcW+Js33wjIw/euHgeSrszx7PmwHYncW2n4VAAAAAAAAAAAAAAAAwmHyF/rTPgSSNQH3AAAAAElFTkSuQmCC" />
                                </defs>
                            </svg>
                        </div>
                        <h3 class="font-medium text-[20px] text-home-primary text-left font-plus">
                                <?php echo e(__('home_features.portable_title')); ?>
                            </h3>
                    </div>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed font-plus">
                        <?php echo e(__('home_features.portable_desc')); ?>
                    </p>
                </div>
            </div>

            <!-- Feature 3: Flexible -->
            <div class="sr sr--fade-up p-[2px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg" style="--sr-delay: 140ms">
                <div class="bg-white w-full h-full rounded-home-card p-6 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 gap-2">
                        <div class="flex justify-start">
                            <div
                                class="w-[36px] sm:mr-2 h-[36px] flex items-center justify-center bg-home-surface-light rounded-home-sm text-home-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-zap-icon lucide-zap">
                                    <path
                                        d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="font-medium text-[20px] text-home-primary text-left font-plus">
                                <?php echo e(__('home_features.flexible_title')); ?>
                            </h3>
                    </div>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed font-plus">
                        <?php echo e(__('home_features.flexible_desc')); ?>

                    </p>
                </div>
            </div>
            <!-- Feature 4: Pretty URLs -->
            <div class="sr sr--fade-up p-[2px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg" style="--sr-delay: 210ms">
                <div class="bg-white w-full h-full rounded-home-card p-6 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 gap-2">
                        <div class="flex justify-start">
                            <svg width="36" height="36" class="sm:mr-2" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <rect x="6" y="6" width="24" height="24" fill="url(#pattern0_186_6790)" />
                                <defs>
                                    <pattern id="pattern0_186_6790" patternContentUnits="objectBoundingBox" width="1"
                                        height="1">
                                        <use xlink:href="#image0_186_6790" transform="scale(0.01)" />
                                    </pattern>
                                    <image id="image0_186_6790" width="100" height="100" preserveAspectRatio="none"
                                        xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEeElEQVR4nO2cW49URRDHT3bfxPvtGZUI2y2rCR/EBFljNAv4pqjPPm4iWcStmiUkvpOQxejyIbwkKHIL4BL5AiLIVM2wPuJyTJ0ZTYzJdJ/pc3b2zPn/kn7tU9X/vlR31UyWAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAKiXefrzRdeRBUey7FnPe5bvHcuV1OZZT6baZn1UYYsj+c58Mx8ddQ+9cXrzhWwnsf/z/jOO9RPHesmTPPKseeWNdD3VTuujHtsKny861o9eP9V7OpukEDbrPOtmLY5yQwT5r50PPMuJbRfGky46lnu1O8gNE2TYHOldx/peVjd7v7j/hGc9t21CcDMF+VcYlrPuy3uPZ3Ud2J7l6raLwc0VZCjKFRu7rPLoifX2RMTgZgsyEEVvVxaNDbapCa0Mng5BClFILs/T77uSBRnnzHAsDx3pBU+y5llXh9HY2M1VcEBaH6l2FL6QrDnWH83HMcblbJoYpIslZ8F139HDrtN/Npty9p148FzhK8uNUmO0ou8mXPgiQ1uS+yZetpTPZG1jKZ9xpEc8Szdu0urdse4pwyUao/ov89R7KWs5fqX/iifdiNxJlsvfwotbZ1iMPae7T9bmZcM4cFKfihHFsfZLrRJ7mwqLIV2bFbV62ED2nurtdiR/hLd5PRbdafFQGO5wsVbPGszgTAmO30/xN/LAq61FFq08wGNZymc8ybVAIPToVd58PtjXHMnbQXU7ejhLwLa6uVXxO7n5xO3Yce9o+HDvHgp3NEgujVodDy0GTzHWs96KjODyCbZbKT7aGAUvjySfxQzW+dGq6oUUQ9siiGHnxOjJrd9kITzLDwFV14KdBL/RGkG+Gi2IfBvRyejDyJFSsqFtEYSVR39DroY7CUUHrFyBoe0QhLSTLkhoy2I9l2xoSwRxpF+nb1k41PMddagHw16Sv6IuNC0XZF9VYa8VuoWMtaeBFGNbcTGk3vtB0Tvdt4IdWe43+HRCenNhPZ9NMXiaWVjPZ+0lfLQgshW901glXt2rZJpxEauj1FnsSD8OLjcScZ3+nlo9ayCWrCsyqIHxe43kg+hOLXkSlaAi3bCkTK0eNi1BFRGwOJJe6XGzGtWoaIR0Y/9q/+Ws5XhL4UZGj47leOkP2CqxhHxciCjd4kxpYY5kYT2fLc4MEomcwHfGTnsPa5miY3eLviwPkPo83wQsQhrmPALR1P/O3neSPmzFXaU+OLw8FrfUwWvnqiP5NKXNreibqQNofaTaMSiUK3y6WPhYclw8yZlUPzKr3rYyyNIfr7JR80tJPenPB5Z+eyyrArssothax26O9NfKf/pm5aFW0wpBtJwYrJcq/znCP1j19jhnSmsFITlT2TY1CisYjg+JWygI6Z3kaGq8e4osR5actkIQx9q3S99EXy+G9azHBskY2WqfILJV/BaG9cMd94xklyV747dZYtkwS1FO3R8HsPlU+HbcU+9gGy7BAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAkE2avwE+y+4akrfnRgAAAABJRU5ErkJggg==" />
                                </defs>
                            </svg>
                        </div>
                        <h3 class="font-medium text-[20px] text-home-primary text-left font-plus">
                                <?php echo e(__('home_features.pretty_urls_title')); ?>
                            </h3>
                    </div>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed font-plus">
                        <?php echo e(__('home_features.pretty_urls_desc')); ?>
                    </p>
                </div>
            </div>

            <!-- Feature 5: Integrated CMDER -->
            <div class="sr sr--fade-up p-[2px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg" style="--sr-delay: 280ms">
                <div class="bg-white w-full h-full rounded-home-card p-6 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 gap-2">
                        <div class="flex justify-start">
                            <svg width="36" height="36" class="sm:mr-2" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <rect x="6" y="6" width="24" height="24" fill="url(#pattern0_186_6797)" />
                                <defs>
                                    <pattern id="pattern0_186_6797" patternContentUnits="objectBoundingBox" width="1"
                                        height="1">
                                        <use xlink:href="#image0_186_6797" transform="scale(0.01)" />
                                    </pattern>
                                    <image id="image0_186_6797" width="100" height="100" preserveAspectRatio="none"
                                        xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEkUlEQVR4nO2cS4gcVRSG2wf4QEWDKKgYH0Nm+tyZRBExkEVAXLhQVER8P8DHQheKL1TEXunM1DmVGDIibhSyUAwSdRGFuFZQBCUxulCMCkNius+pNtGo+Ci5ncksnPR0VboeXd3/Bz8MdPXtc+qboqtv1a1aDQAAAAAAAAAAAAAAAAAAAAAAjpnVHF3sAl1XD1rXjHJcoOvGN0YXlSLhisb8qU7sOce6x4nFiC2GRL8ntmf9PipEBoXtMWL7puzG3eBntwval+YqYzUfPAdHhaU6WlbJgbNzE0KiW8pu0lUu+nouMqZmWhc40X/Kb9AqFWL9e3y2eV7mQijQB8tuzlU13Lo/cyFObKb0xqSq0ZeyF8I6V35jVs2wzkGIDFAgxAYrEGKDFQixwcrQC2H9w4l9QGINEn3IiV3XmeBjvc3PIzm2Dxe2Kb/WYRZCbDv9Th+fbZ7eq16/DYne7sR2lV33MArZS2z31hrx8WnrvmVrfAJJdB+x7YOQTI4K/ZJm7MJ+618Tts4nsc8gpK9GbHuW1xU613HYtkPIsWX32KbWGVn3QXM/n9b5LoKQVA1onW1lLcfLzsQaQUjCkOjjmTfw/35En4aQRNEfxjbFJ9VyZmVjz8lO7EcI6XV0sD6RefHde3oKQnoJCdtjaeqamm6fRRxdTaFeu2ZjdGaa907I/lUQsmzh9lWamkjsGWI7tChT7DcXRI+lGoPtawjpLuTN5PXYw93H0gdS9PUGhHSPJJ4OWWYqxL/mt0kyFrG9ACHd/7OfTFLL1Ib2Jb3G8tsk6kvsUQjp8wxr8uV95/Yay2+TZCz/mRDSXciLSeshto+7jiP2SfK+bBpCugqxd5LWQ0F0mWM7uLR5+6Uu0eWJxxHdBiHdC28m/TL2TLBOOtb3ndgBL8KJvlffoC7p+9c34hP9vBmELJPJ0NbXCsL/oCyip0oLIdFXagVBYq9CSM/on0lPWfte+VXUjRDVFmL+KNlSyxkSe6uofiovxLH+60RvzbyJI72ErZsPfwaEJD9K2A45bl6ZdR/+dJnEfi2yl6EQ4g5nb51bV2XVw0TYWuvHLLyPIRISO7HfJ0O7q+/6Q7vHj1VKD0MmJF7IR2l+gR+BuEWObWuptQ+pkLizxpH1Xcd2N4XtFV0lhO0VnSPCbzsI6yKHV4gthkT/cqJfLMxFvebj/+7c6dh5rfwaR0qIq1IgxAYrEGKDFQixERAiurn0xqSq0c2ZC/HLx8pvzKoZtuczF1IP7IbSG5OKhu36nB5apq3Sm5OKhbW5NvzplFoeFHIPkwxXSOyRWm7E8XEk9nbZTbqKxF8E8/ssPyGLq1otGLjpCRmcdPYN23SaO2j6Zmq2OU5ss47tUyc2T6I2ynFi8wv7YsYvcyhMBAAAAAAASMtkEN1EbDtI9HNE8w/bjrpENx5VBondWfYPKjeiocDuWCqk6AeyIPGiELadS4T4WcmyC3MjGmLdX87SLiQ+qhDRbUuE+Mchkeh3ZRfnRi2s33Z9Yp5/Qo7/1vdP+kQ09/irsH6fJzwJBgAAAAAAAAAAAAAAAFCrCv8ByAw4D3FS34EAAAAASUVORK5CYII=" />
                                </defs>
                            </svg>
                        </div>
                        <h3 class="font-medium text-[20px] text-home-primary text-left font-plus">
                                <?php echo e(__('home_features.integrated_cmder_title')); ?>
                            </h3>
                    </div>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed font-plus">
                        <?php echo e(__('home_features.integrated_cmder_desc')); ?>
                    </p>
                </div>
            </div>
            <!-- Feature 6: Mail Catcher -->
            <div class="sr sr--fade-up p-[2px] bg-gradient-to-r from-home-accent to-home-primary rounded-home-lg" style="--sr-delay: 350ms">
                <div class="bg-white w-full h-full rounded-home-card p-6 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 gap-2">
                        <div class="flex justify-start sm:justify-start">
                            <svg width="36" height="36" class="sm:mr-2" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <rect width="36" height="36" rx="2.4" fill="var(--home-surface-light)" />
                                <rect x="6" y="6" width="24" height="24" fill="url(#pattern0_186_6804)" />
                                <defs>
                                    <pattern id="pattern0_186_6804" patternContentUnits="objectBoundingBox" width="1"
                                        height="1">
                                        <use xlink:href="#image0_186_6804" transform="scale(0.01)" />
                                    </pattern>
                                    <image id="image0_186_6804" width="100" height="100" preserveAspectRatio="none"
                                        xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAFT0lEQVR4nO2dTYgcRRTHe+P3t1EPfgc1JjPv7eyqixpEDX4c/UBwrx5FvOTgwYjoBA+6O/V6ZSEegpJDQJH4dYhBQUguigpKTDAoKmgiMe7uvDezauJnbKnZ3cxq1mSnprurevb94H9Z2K569Z/qqnpV3R1FiqIoiqIoiqIoiqIoiqIoiqIoyoKsHp06p0yN64HkPoh5WMXDti0q1ByCjRNn52YExjKIJC8jya9IkqjkGIGRw2Dk1ZLh/kzNACNPgOE/fAeMBZFtKyBZn5UZT/sOEAsqIKmmagYS3+I7KCywwPBfJZpclaIhQr6DwqIrlodSMwRInvIeEBVcceOO1AxB4iuAWLwHRcUUGHk3SpK+1AyZN93d6zs4LNjYAcTjK6rfnh5lwdCm5BQkWQckv/gOFgMXEO8um/rNUR5UxppXo5F3fAeNgS4K0fAGqCanRnmDRu4Fku99NwIGIiDemer01oXB5xvn2/ukvV/6bhD0JcMMxA9HIdFKqhF/ssSM+BuIt6yiny6KQmRtNTnZDvpo5GfvjUXZCoi/Kdfqd0dFYDCuXwbEb/SkEaaVXB3JbCqb+aBvZH/vmCEflMcYoyCoJstc/m1oRM5DwxuR+EiBe0Wj3/AjLm2wJt5/hr212btGlAb2QkjyUWsWZeSV0tjkJS7XsYskJN7lu3GxUxnZ6hozktyDJPtme9fh8qjcFnULEL/wr18LSRONPOrya7GDPhh+rCAr/X32luvSZtZAa+Sx1+TNUbcsfOHWxT9FM3WjyzVXj05diiSvBdDoyQJx2VvrJntmoOPAkqTPptmRuP5/vS3KzpDW1O9Puyh0qvzsoD/XpYuefyqPTl8Lhncct4ysDZmnA2DqD7pcf6h64Ew7jfS50ocu8k824QqGH0fDv52wrBwNmQ2Mt8GIXOlSDtQa16GRj/M3g3e45p/KRm4HI18sury8DZnp9nLI/mKGtyYndVxYNVlmc0JoZDqHXvGj67Zq5bnmcjvO2NRJR+X6MKQt3gVx/SbXWQoQbwkx/zRzII4n3Mr2akh7xrJyvH6u+zyev0vRjK9d8092D8huxXZXvndDjlbkB9fbw5Ad9O2A28UBvW7yT6kmTIMxpN0w28pGVjjv6Rv5sOMySd53zT+VRhs3pLqlEJohsw10yHmLs7r4Qb+VUSBZ55JRGDAHz8pkKh6iIe0G492luL7GpU5Qm7j4eIO+7YmVkfrlwS1WQzZkRnzEZoJtRtilbmVq3IVGtoPhyZZI3rZ/c06iGn4ru1gLYchRHUz1qGUnJElfXmufIhkyV+HtA6ZxVZQTlVgqLhOFpWMI5XOuyW4a2TKQ+Pc8YyukIdg2Zo99DCJKmf5Y1gLJlz5iKrQhOC/NUXp2+sJu44C4eYFT/kkNkVQTgTNm8DAanvIdR88YgnPGEO/sp6nSoutea16DJO/5rnfPGoLzBv2V48lpJ9w0Cu1J4V40BNvau9AiEEzjTjTyeQD1W3KGJHMpGDDy4oz4M9/1WfKGYJGkhkhYUkMkLKkhEpbUEAlLaoiEJTVEwpIaImFJDZGwpIZIWFJDJCypIRKW1BAJS2qIhCU1RMKSGiJhSQ2RHjSEeLP3QKg3BCQvpdBD+BnfgWCvyPCGVE76eQ+EekNAcmvXhtinaZH4K9/BYMFlj686PZm8YC+pNR7wHRAWXPZTFlGa6OvGpRszalEmD7a0vpBQ3PdeYe5qtdWTqb/V+r9PwwLxm60jnd4DlpA/6PJ6f00GorywT6raN+eUa3K/908NxWHItoVtE/tsfW5GKIqiKIqiKIqiKIqiKIqiKIqiRMXiHxw5JPqj0QkuAAAAAElFTkSuQmCC" />
                                </defs>
                            </svg>
                        </div>
                        <h3 class="font-medium text-[20px] text-home-primary text-left font-plus">
                                <?php echo e(__('home_features.mail_catcher_title')); ?>
                            </h3>
                    </div>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed font-plus">
                        <?php echo e(__('home_features.mail_catcher_desc')); ?>

                    </p>
                </div>
            </div>
        </div>

        <!-- Technology Stack -->
        <div class="mt-16">
            <h2 class="sr sr--fade-up text-[24px] font-medium text-center text-gray-800 mb-6 font-plus" style="--sr-delay: 0ms">
                <?php echo e(__('home_features.stack_heading')); ?>
            </h2>
        
            <!-- Mobile + Tablet: grid | Desktop: flex -->
            <div class="sr sr--fade-up grid grid-cols-5 gap-y-3 text-center
                        lg:flex lg:flex-row lg:justify-center lg:gap-x-12" style="--sr-delay: 60ms">
        
                <!-- Row 1 (5 item) -->
                <span class="text-[18px] sm:text-[24px] font-bold text-gray-500 font-space"><?php echo e(__('home_features.stack_php')); ?></span>
                <span class="text-[18px] sm:text-[24px] font-bold text-gray-500 font-space"><?php echo e(__('home_features.stack_node')); ?></span>
                <span class="text-[18px] sm:text-[24px] font-bold text-gray-500 font-space"><?php echo e(__('home_features.stack_python')); ?></span>
                <span class="text-[18px] sm:text-[24px] font-bold text-gray-500 font-space"><?php echo e(__('home_features.stack_go')); ?></span>
                <span class="text-[18px] sm:text-[24px] font-bold text-gray-500 font-space"><?php echo e(__('home_features.stack_ruby')); ?></span>
                <span class="col-start-2 lg:col-auto text-[18px] sm:text-[24px] font-bold text-gray-500 font-space"><?php echo e(__('home_features.stack_mysql')); ?></span>
                <span class="text-[18px] sm:text-[24px] font-bold text-gray-500 font-space"><?php echo e(__('home_features.stack_nginx')); ?></span>
                <span class="text-[18px] sm:text-[24px] font-bold text-gray-500 font-space"><?php echo e(__('home_features.stack_apache')); ?></span>
        
            </div>
        </div>
    </div>
</section>