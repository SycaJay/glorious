<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Jerusalem Photo Gallery</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Photo Collage Frames with Mixed Shapes */
        .collage-frame {
            position: absolute;
            opacity: 0.7;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transform: rotate(var(--rotate));
            transition: all 0.3s ease-out;
        }

        .collage-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.9);
        }

        .collage-frame:hover {
            transform: rotate(var(--rotate)) scale(1.05);
            opacity: 0.9;
        }

        /* Animation for Collage Frames */
        @keyframes assembleCollage {
            0% {
                transform: translate(var(--start-x), var(--start-y)) rotate(var(--rotate)) scale(0.8);
                opacity: 0;
            }
            100% {
                transform: translate(0, 0) rotate(var(--rotate)) scale(1);
                opacity: 0.7;
            }
        }

        .animate-collage {
            animation: assembleCollage 0.3s ease-out forwards;
        }

        /* Modern Animation for Welcome Text */
        @keyframes fadeInLetters {
            0% {
                opacity: 0;
                letter-spacing: 10px;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                letter-spacing: normal;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-letters {
            animation: fadeInLetters 1.5s ease-out forwards;
        }

        .animate-fade-in-up {
            animation: fadeInUp 1s ease-out forwards;
            opacity: 0;
        }

        /* Static Navigation Bar */
        .nav-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 20;
        }

        /* Responsive Adjustments */
        @media (max-width: 640px) {
            .collage-frame {
                width: 100px !important;
                height: 100px !important;
            }
            .text-6xl {
                font-size: 2rem !important;
            }
            .text-xl {
                font-size: 1rem !important;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .collage-frame {
                width: 150px !important;
                height: 150px !important;
            }
        }
    </style>
</head>
<body>
    <div id="root"></div>

    <!-- React and ReactDOM CDN -->
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <!-- Babel for JSX -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <script type="text/babel">
        const { useEffect, useState } = React;

        const App = () => {
            const [imagesLoaded, setImagesLoaded] = useState(false);

            // Preload all images
            useEffect(() => {
                const imageUrls = [
                    '../assets/photo1.jpg',
                    '../assets/photo2.jpg',
                    '../assets/photo3.jpg',
                    '../assets/photo4.jpg',
                    '../assets/photo5.jpg',
                    '../assets/photo6.jpg',
                    '../assets/photo7.jpg',
                    '../assets/photo8.jpg',
                    '../assets/photo9.jpg',
                    '../assets/photo10.jpg',
                    '../assets/photo11.jpg',
                    '../assets/photo12.jpg',
                    '../assets/photo13.jpg',
                    '../assets/photo14.jpg',
                    '../assets/photo15.jpg',
                    '../assets/photo16.jpg',
                    '../assets/photo17.jpg',
                    '../assets/photo18.jpg',
                    '../assets/photo19.jpg',
                    '../assets/photo20.jpg',
                    '../assets/photo21.jpg',
                    '../assets/photo22.jpg',
                    '../assets/photo23.jpg',
                    '../assets/photo24.jpg',
                    '../assets/photo25.jpg',
                    '../assets/photo26.jpg',
                    '../assets/photo27.jpg',
                    '../assets/photo28.jpg',
                    '../assets/photo29.jpg',
                    '../assets/photo30.jpg'
                ];

                const preloadImages = async () => {
                    const promises = imageUrls.map((url) => {
                        return new Promise((resolve, reject) => {
                            const img = new Image();
                            img.src = url;
                            img.onload = resolve;
                            img.onerror = reject;
                        });
                    });

                    try {
                        await Promise.all(promises);
                        setImagesLoaded(true);
                    } catch (error) {
                        console.error('Error preloading images:', error);
                        setImagesLoaded(true); // Fallback to show content even if some images fail
                    }
                };

                preloadImages();
            }, []);

            useEffect(() => {
                if (imagesLoaded) {
                    const frames = document.querySelectorAll('.collage-frame');
                    const welcomeText = document.querySelector('.welcome-text');
                    const subText = document.querySelector('.sub-text');
                    const buttons = document.querySelectorAll('.explore-button');

                    frames.forEach((frame, index) => {
                        setTimeout(() => {
                            frame.classList.add('animate-collage');
                        }, index * 10);
                    });

                    setTimeout(() => {
                        welcomeText.classList.add('fade-in-letters');
                        subText.classList.add('animate-fade-in-up');
                        buttons.forEach(button => button.classList.add('animate-fade-in-up'));
                    }, 500);
                }
            }, [imagesLoaded]);

            return (
                <div className="min-h-screen relative overflow-hidden">
                    {/* Background Photo Collage */}
                    <div className="fixed inset-0 bg-gray-100">
                        {/* 30 Collage Frames with Mixed Shapes, Spread Across Page */}
                        <div className="collage-frame" style={{ width: '200px', height: '200px', top: '2%', left: '2%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '-5deg', '--start-x': '-300px', '--start-y': '-300px' }}>
                            <img src="../assets/photo1.jpg" alt="Photo 1" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '250px', height: '180px', top: '5%', left: '15%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '3deg', '--start-x': '300px', '--start-y': '-250px' }}>
                            <img src="../assets/photo2.jpg" alt="Photo 2" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '220px', top: '3%', left: '28%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '-2deg', '--start-x': '400px', '--start-y': '-200px' }}>
                            <img src="../assets/photo3.jpg" alt="Photo 3" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '220px', height: '180px', top: '8%', left: '40%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '4deg', '--start-x': '500px', '--start-y': '-150px' }}>
                            <img src="../assets/photo4.jpg" alt="Photo 4" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '200px', height: '200px', top: '6%', left: '52%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '-3deg', '--start-x': '600px', '--start-y': '-100px' }}>
                            <img src="../assets/photo5.jpg" alt="Photo 5" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '240px', top: '4%', left: '65%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '2deg', '--start-x': '700px', '--start-y': '-50px' }}>
                            <img src="../assets/photo6.jpg" alt="Photo 6" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '240px', height: '180px', top: '2%', left: '78%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '-4deg', '--start-x': '800px', '--start-y': '0px' }}>
                            <img src="../assets/photo7.jpg" alt="Photo 7" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '200px', height: '250px', top: '20%', left: '5%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '5deg', '--start-x': '-350px', '--start-y': '300px' }}>
                            <img src="../assets/photo8.jpg" alt="Photo 8" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '220px', height: '180px', top: '18%', left: '18%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '-2deg', '--start-x': '250px', '--start-y': '350px' }}>
                            <img src="../assets/photo9.jpg" alt="Photo 9" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '200px', top: '22%', left: '30%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '3deg', '--start-x': '400px', '--start-y': '300px' }}>
                            <img src="../assets/photo10.jpg" alt="Photo 10" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '250px', height: '200px', top: '20%', left: '42%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '-4deg', '--start-x': '500px', '--start-y': '250px' }}>
                            <img src="../assets/photo11.jpg" alt="Photo 11" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '200px', height: '240px', top: '24%', left: '55%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '2deg', '--start-x': '600px', '--start-y': '200px' }}>
                            <img src="../assets/photo12.jpg" alt="Photo 12" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '220px', top: '22%', left: '68%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '-3deg', '--start-x': '700px', '--start-y': '150px' }}>
                            <img src="../assets/photo13.jpg" alt="Photo 13" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '220px', height: '180px', top: '20%', left: '81%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '4deg', '--start-x': '800px', '--start-y': '100px' }}>
                            <img src="../assets/photo14.jpg" alt="Photo 14" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '200px', height: '250px', top: '40%', left: '2%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '-5deg', '--start-x': '-300px', '--start-y': '400px' }}>
                            <img src="../assets/photo15.jpg" alt="Photo 15" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '200px', top: '38%', left: '15%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '2deg', '--start-x': '250px', '--start-y': '450px' }}>
                            <img src="../assets/photo16.jpg" alt="Photo 16" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '240px', height: '180px', top: '42%', left: '28%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '-3deg', '--start-x': '400px', '--start-y': '400px' }}>
                            <img src="../assets/photo17.jpg" alt="Photo 17" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '200px', height: '220px', top: '40%', left: '41%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '4deg', '--start-x': '500px', '--start-y': '350px' }}>
                            <img src="../assets/photo18.jpg" alt="Photo 18" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '220px', height: '180px', top: '44%', left: '54%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '-2deg', '--start-x': '600px', '--start-y': '300px' }}>
                            <img src="../assets/photo19.jpg" alt="Photo 19" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '240px', top: '42%', left: '67%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '3deg', '--start-x': '700px', '--start-y': '250px' }}>
                            <img src="../assets/photo20.jpg" alt="Photo 20" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '200px', height: '200px', top: '60%', left: '5%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '-4deg', '--start-x': '-350px', '--start-y': '500px' }}>
                            <img src="../assets/photo21.jpg" alt="Photo 21" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '220px', height: '180px', top: '58%', left: '18%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '5deg', '--start-x': '250px', '--start-y': '550px' }}>
                            <img src="../assets/photo22.jpg" alt="Photo 22" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '200px', top: '62%', left: '30%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '-2deg', '--start-x': '400px', '--start-y': '500px' }}>
                            <img src="../assets/photo23.jpg" alt="Photo 23" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '250px', height: '200px', top: '60%', left: '42%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '3deg', '--start-x': '500px', '--start-y': '450px' }}>
                            <img src="../assets/photo24.jpg" alt="Photo 24" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '200px', height: '240px', top: '64%', left: '55%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '-4deg', '--start-x': '600px', '--start-y': '400px' }}>
                            <img src="../assets/photo25.jpg" alt="Photo 25" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '220px', top: '62%', left: '68%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '2deg', '--start-x': '700px', '--start-y': '350px' }}>
                            <img src="../assets/photo26.jpg" alt="Photo 26" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '220px', height: '180px', top: '80%', left: '5%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '-3deg', '--start-x': '-300px', '--start-y': '600px' }}>
                            <img src="../assets/photo27.jpg" alt="Photo 27" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '200px', height: '200px', top: '78%', left: '18%', clipPath: 'polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0 80%, 0 20%)', '--rotate': '4deg', '--start-x': '250px', '--start-y': '650px' }}>
                            <img src="../assets/photo28.jpg" alt="Photo 28" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '180px', height: '240px', top: '82%', left: '30%', clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)', '--rotate': '-5deg', '--start-x': '400px', '--start-y': '600px' }}>
                            <img src="../assets/photo29.jpg" alt="Photo 29" loading="lazy" />
                        </div>
                        <div className="collage-frame" style={{ width: '220px', height: '180px', top: '80%', left: '42%', clipPath: 'circle(50% at 50% 50%)', '--rotate': '2deg', '--start-x': '500px', '--start-y': '550px' }}>
                            <img src="../assets/photo30.jpg" alt="Photo 30" loading="lazy" />
                        </div>
                    </div>

                    {/* Static Navigation Bar */}
                    <nav className="nav-bar bg-white shadow-lg">
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <div className="flex items-center h-16">
                                <div className="flex-shrink-0 flex items-center">
                                    <span className="text-2xl mr-2"><i className="fas fa-camera-retro"></i></span>
                                    <span className="text-xl font-bold text-gray-900">New Jerusalem Photo Gallery</span>
                                </div>
                            </div>
                        </div>
                    </nav>

                    {/* Main Content - Shown Only After Images Load */}
                    {imagesLoaded && (
                        <div className="relative z-10 min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 pt-16">
                            <div className="text-center">
                                <h1 className="welcome-text text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                                    Welcome to New Jerusalem Photo Gallery
                                </h1>
                                
                                <p className="sub-text mt-3 max-w-lg mx-auto text-lg text-gray-800 sm:text-xl md:mt-5 bg-white/80 p-4 rounded-lg">
                                    A church gallery showcasing cherished moments from our conferences, services, and events. 
                                    Access and relive the memories captured in time.
                                </p>
                                
                                <div className="mt-10 flex justify-center space-x-6">
                                    <a href="index.php" className="explore-button flex items-center bg-blue-600 text-white px-6 py-3 rounded-full font-semibold 
                                        hover:bg-blue-700 transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                                        <i className="fas fa-home mr-2"></i> Home
                                    </a>
                                    <a href="photos.php" className="explore-button flex items-center bg-indigo-600 text-white px-6 py-3 rounded-full font-semibold 
                                        hover:bg-indigo-700 transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                                        <i className="fas fa-images mr-2"></i> Explore Photos
                                    </a>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            );
        };

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>